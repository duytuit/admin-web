<?php

namespace App\Services\FCM\V2;

use App\Helpers\dBug;
use App\Models\CampainDetail;
use App\Repositories\Fcm\FcmRespository;
use App\Repositories\NotifyLog\NotifyLogRespository;
use LaravelFCM\Message\OptionsBuilder;
use LaravelFCM\Message\PayloadDataBuilder;
use LaravelFCM\Message\PayloadNotificationBuilder;
use Illuminate\Support\Facades\Redis;
use App\Models\Fcm as DeviceToken;
use App\Models\PublicUser\V2\TokenUser;
use App\Models\PublicUser\V2\TokenUserPush;
use App\Models\PublicUser\V2\UserInfo;
use App\Services\RedisCommanService;
use App\Util\Debug\Log;
use LaravelFCM\Facades\FCM;

class SendNotifyFCMService
{
    const NEW_POST_EVENT = 'event';
    const NEW_POST_ARTICLE = 'article';
    const NEW_POST_VOUCHER = 'voucher';
    const NEW_POST_FEEDBACK = 'feedback';
    const CREATE_ORDER_FREE_EVENT = 'event';
    const CREATE_ORDER_FREE_ARTICLE = 'article';
    const CREATE_ORDER_FREE_VOUCHER = 'voucher';
    const CREATE_ORDER_FREE_FEEDBACK = 'feedback';
    const ACCEPT_ORDER_EVENT = 'event';
    const ACCEPT_ORDER_ARTICLE = 'article';
    const ACCEPT_ORDER_VOUCHER = 'voucher';
    const ACCEPT_ORDER_FEEDBACK = 'feedback';
    const NHAC_NO = 'nhac_no';

    const BILL_NEW = "bill";
    const NEW_TASK = 'task';
    const NOTIFY_TRANSACTION_PAYMENT = 'notify_transaction_payment';
    const STATUS_NEW = 0;

    private $log;
    private $modelFCM;
    public function __construct(  NotifyLogRespository $log,FcmRespository $modelFCM)
    {
        $this->log = $log;
        $this->modelFCM = $modelFCM;
    }


    public static function send($message = 'chi la demo thoi ma 3', $user_infor_id, $data_payload = ['something' => 'payload demo'], $title_noti = 'Thông Báo', $campains)
    {
        $userInfo = UserInfo::find($user_infor_id);
        if (!$userInfo) {
            return false;
        } 
        try {
         
                $optionBuilder = new OptionsBuilder();
                $optionBuilder->setTimeToLive(60*20);
                $optionBuilder->setPriority('normal');
                $optionBuilder->setContentAvailable(true);

                $notificationBuilder = new PayloadNotificationBuilder($title_noti);
                $notificationBuilder->setBody($message)
                            ->setSound('sound');

                $dataBuilder = new PayloadDataBuilder();
                $dataBuilder->addData(['data'=>json_encode($data_payload)] );

                $option = $optionBuilder->build();
                $notification = $notificationBuilder->build();
                $data = $dataBuilder->build();
               
                //
                $rs =  TokenUserPush::where('user_id',$userInfo->user_id)->get();
                if ($rs->count() == 0) {
                    return false;
                }  
                foreach ($rs as $key => $value) {
                        $config_type = $value->bundle_id == 'com.portalbeanz.ash' ||  $value->bundle_id == 'com.asahi.bdc' ? 'asahi' : 'cudan';
                        $config=[
                            'app_config'=>'fcm.'.$config_type
                        ];
                        $downstreamResponse = FCM::sendTo($value->token, $option, $notification, $data, $config['app_config']);
                        if($downstreamResponse->numberSuccess() == 1){
                            CampainDetail::create([
                                'campain_id' => @$campains->id,
                                'type' => 'app',
                                'building_id' => @$campains->bdc_building_id,
                                'type_campain' =>@$campains->type,
                                'view' => 0,
                                'contact' => $userInfo->user_id,
                                'status' => true,
                                'reason' => $userInfo->full_name,
                                'content' => $data_payload
                            ]);  
                        }else{
                            CampainDetail::create([
                                'campain_id' => @$campains->id,
                                'type' => 'app',
                                'building_id' => @$campains->bdc_building_id,
                                'type_campain' =>@$campains->type,
                                'view' => 0,
                                'contact' => $userInfo->user_id,
                                'status' => false,
                                'reason' => json_encode($downstreamResponse),
                                'content' => $data_payload
                            ]); 
                        }
                                       
                }

                return [
                    'numberSuccess'=>$downstreamResponse->numberSuccess(),
                    'numberFailure'=>$downstreamResponse->numberFailure(),
                    'numberModification'=>$downstreamResponse->numberModification(),
                ];

        } catch (\Exception $e) {
            CampainDetail::create([
                'campain_id' => @$campains->id,
                'type' => 'app',
                'building_id' => @$campains->bdc_building_id,
                'type_campain' =>@$campains->type,
                'view' => 0,
                'contact' => $userInfo->user_id,
                'status' => false,
                'reason' => $e->getMessage(),
                'content' => $data_payload,
            ]);
        }

    }

    public function sendNotify($message = 'chi la demo thoi ma 3', $user_infor_id, $data_payload=['something' => 'payload demo'], $prioryty = 'normal', $content_available = true ,$title_noti='Thông Báo',$campains=null)
    {
        $userInfo = UserInfo::find($user_infor_id);
        if (!$userInfo) {
            return false;
        } 
        try {
         
                $optionBuilder = new OptionsBuilder();
                $optionBuilder->setTimeToLive(60*20);
                $optionBuilder->setPriority($prioryty);
                $optionBuilder->setContentAvailable($content_available);

                $notificationBuilder = new PayloadNotificationBuilder($title_noti);
                $notificationBuilder->setBody($message)
                            ->setSound('sound');

                $dataBuilder = new PayloadDataBuilder();
                $dataBuilder->addData(['data'=>json_encode($data_payload)] );

                $option = $optionBuilder->build();
                $notification = $notificationBuilder->build();
                $data = $dataBuilder->build();
               
                //
                $rs = $this->getToken($userInfo->user_id);
                if ($rs->count() == 0) {
                    return false;
                }  
                foreach ($rs as $key => $value) {
                        $config_type = $value->bundle_id == 'com.portalbeanz.ash' ||  $value->bundle_id == 'com.asahi.bdc' ? 'asahi' : 'cudan';
                        $config=[
                            'app_config'=>'fcm.'.$config_type
                        ];
                        $downstreamResponse = FCM::sendTo($value->token, $option, $notification, $data, $config['app_config']);
                        if($downstreamResponse->numberSuccess() == 1){
                            CampainDetail::create([
                                'campain_id' => @$campains->id,
                                'type' => 'app',
                                'building_id' => @$campains->bdc_building_id,
                                'type_campain' =>@$campains->type,
                                'view' => 0,
                                'contact' => $userInfo->user_id,
                                'status' => true,
                                'reason' => $userInfo->full_name,
                                'content' => $data_payload
                            ]);  
                        }else{
                            CampainDetail::create([
                                'campain_id' => @$campains->id,
                                'type' => 'app',
                                'building_id' => @$campains->bdc_building_id,
                                'type_campain' =>@$campains->type,
                                'view' => 0,
                                'contact' => $userInfo->user_id,
                                'status' => false,
                                'reason' => json_encode($downstreamResponse),
                                'content' => $data_payload
                            ]); 
                        }
                                       
                }

                return [
                    'numberSuccess'=>$downstreamResponse->numberSuccess(),
                    'numberFailure'=>$downstreamResponse->numberFailure(),
                    'numberModification'=>$downstreamResponse->numberModification(),
                ];

        } catch (\Exception $e) {
            CampainDetail::create([
                'campain_id' => @$campains->id,
                'type' => 'app',
                'building_id' => @$campains->bdc_building_id,
                'type_campain' =>@$campains->type,
                'view' => 0,
                'contact' => $userInfo->user_id,
                'status' => false,
                'reason' => $e->getMessage(),
                'content' => $data_payload,
            ]);
        }

    }
    public static function testPushV2($token , $message, $data_payload=['something' => 'payload demo'],$type_config='cudan')
    {
        try {
            //$config = $this->configOffApp($app_id);
            $data_payload['message']= '[123'.'-] '.$data_payload['message'];

            $optionBuilder = new OptionsBuilder();
            $optionBuilder->setTimeToLive(60*20);
            $optionBuilder->setPriority('normal');
            $optionBuilder->setContentAvailable('');

            $notificationBuilder = new PayloadNotificationBuilder('Thông Báo');
            $notificationBuilder->setBody($message)
                ->setSound('sound');

            $dataBuilder = new PayloadDataBuilder();
            $dataBuilder->addData(['data'=>json_encode($data_payload)] );

            $option = $optionBuilder->build();
            $notification = $notificationBuilder->build();
            $data = $dataBuilder->build();
            $config_type = $type_config == 'com.portalbeanz.ash' || $type_config == 'com.asahi.bdc' ? 'asahi' : 'cudan';

            $config_type = $type_config == 'com.dxmb.bms' ? 'banquanly' : $config_type;

            $config=[
                'app_config'=>'fcm.'.$config_type
            ];
            $downstreamResponse = FCM::sendTo($token, $option, $notification, $data, $config['app_config']);

            return [
                'mess' => "success",
                'status' => "success",
                'numberSuccess'=>$downstreamResponse->numberSuccess(),
                'numberFailure'=>$downstreamResponse->numberFailure(),
                'numberModification'=>$downstreamResponse->numberModification(),
            ];

        } catch (\Exception $e) {

            return [
                'mess' => "error || ".$e->getMessage(),
                'status' => "fail",
                'token' => $token,
                'config' => $config['app_config'],
                'server_key' => env('FCM_BANQUANLY_SERVER_KEY'),
                'sender_id' => env('FCM_BANQUANLY_SENDER_ID'),
            ];
        }
    }

    public static function pushNotify($token, $data_payload, $type_config)
    {

        try {
               
                $optionBuilder = new OptionsBuilder();
                $optionBuilder->setTimeToLive(60*20);
                $optionBuilder->setPriority('normal');
                $optionBuilder->setContentAvailable(true);

                $notificationBuilder = new PayloadNotificationBuilder($data_payload->title);
                $notificationBuilder->setBody($data_payload->message)
                            ->setSound('sound');

                $dataBuilder = new PayloadDataBuilder();
                $dataBuilder->addData(['data'=>json_encode($data_payload)] );

                $option = $optionBuilder->build();
                $notification = $notificationBuilder->build();
                $data = $dataBuilder->build();
              
                $config_type = $type_config == 'com.portalbeanz.ash' || $type_config == 'com.asahi.bdc' ? 'asahi' : 'cudan';
                $config_type = $type_config == 'com.dxmb.bms' ? 'banquanly' : $config_type;
                $config=[
                    'app_config'=>'fcm.'.$config_type
                ];
               
                $downstreamResponse = FCM::sendTo($token, $option, $notification, $data, $config['app_config']);

                return [
                    'numberSuccess'=>$downstreamResponse->numberSuccess(),
                    'numberFailure'=>$downstreamResponse->numberFailure(),
                    'numberModification'=>$downstreamResponse->numberModification(),
                ];

        } catch (\Exception $e) {
           
        }

    }


    private function getToken($user_id)
    {
        return  TokenUserPush::where('user_id',$user_id)->get();
    }

    private function writeLog($data)
    {
        return $this->log->create($data);
    }

    public static function setItemForQueueNotify( $data=[])
    {
       return self::queueSet(env('REDIS_QUEUE_PREFIX'). 'REDIS_SEND_APP_Campain_v2_' . $data['campain_id'],json_encode($data));
    }

    public static function getItemForQueueNotify($id)
    {
        $rs = json_decode(self::queuePop([env('REDIS_QUEUE_PREFIX') . 'REDIS_SEND_APP_Campain_v2_' . $id ]), true);

        if($rs){
            //  đang gửi app
            RedisCommanService::setKey(env('REDIS_QUEUE_PREFIX') . 'REDIS_SEND_APP_Campain_v2_running', json_encode(['id'=>$id, 'started_at'=>strtotime(now())]));
        }
        return $rs;    
    }

    // Tạo hàng đợi thong bao cho campain
    public static function setQueueAppCampain($id)
    {   
        
        return self::queueSet(env('REDIS_QUEUE_PREFIX') . 'REDIS_APP_QUEUE', $id);
    }
    // pop campain trong hàng đợi 
    public static function popQueueAppCampain()
    {   
        return RedisCommanService::queuePop(env('REDIS_QUEUE_PREFIX') . 'REDIS_APP_QUEUE');
    }
    // Lây campain id trong hàng đợi 
    public static function getQueueAppCampain()
    {   
        return RedisCommanService::queueRange(env('REDIS_QUEUE_PREFIX') . 'REDIS_APP_QUEUE', 0,0);
    }   

    public static function queueSet($key, $data)
    {
        return Redis::command('rpush', [$key, [$data]]);
    }

    public static function queuePop($key=[])
    {
        return Redis::command('lpop', $key);
    }

    private function deleteToken($token)
    {
        return DeviceToken::whereIn('token', $token)->delete();
    }

    private function changeToken($array)
    {
        foreach ($array as $key => $value) {
            DeviceToken::where('token', $key)->update(['token'=> $value]);
        }
        return true;
    }

}