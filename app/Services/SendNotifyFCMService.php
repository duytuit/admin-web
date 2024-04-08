<?php

namespace App\Services;

use App\Models\SentStatusDetail;
use App\Repositories\Fcm\FcmRespository;
use App\Repositories\NotifyLog\NotifyLogRespository;
use LaravelFCM\Message\OptionsBuilder;
use LaravelFCM\Message\PayloadDataBuilder;
use LaravelFCM\Message\PayloadNotificationBuilder;
use FCM;
//use App\Models\MongoDb\UserNotifyLog;
use Illuminate\Support\Facades\Redis;
use App\Models\System\DeviceToken;

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

    const BILL_NEW = "bill";
    const NEW_TASK = 'task';
    const STATUS_NEW = 0;

    private $log;
    private $modelFCM;

    public function __construct( NotifyLogRespository $log,FcmRespository $modelFCM)
    {
         $this->log = $log;
         $this->modelFCM = $modelFCM;
    }

    private function configOffApp($app_config =null)
    {
        if ($app_config) {
            return [
                'app_config'=>'fcm.'.$app_config,
                'brand_name' => 'Building Care'
            ];
        }
        throw new Exception("Khong co config fcm.".$app_config, 12345);

        // code lay ra config
    }

    public function send($message = 'chi la demo thoi ma 3', $user_id = 19, $data_payload=['something' => 'payload demo'], $prioryty = 'normal', $content_available = '',$title_noti='',$building_id='', $app_config=null)

    // public function send($message = 'chi la demo thoi ma 3', $user_id = 19, $data_payload=['something' => 'payload demo'], $prioryty = 'normal', $content_available = '',$title_noti='',$building_id='')
    {
        $config = $this->configOffApp($app_config);

        $optionBuilder = new OptionsBuilder();
        $optionBuilder->setTimeToLive(60*20);
        $optionBuilder->setPriority($prioryty);
        $optionBuilder->setContentAvailable($content_available);

        // $notificationBuilder = new PayloadNotificationBuilder('Iccorect App');
        // $notificationBuilder->setBody($message)
        //                     ->setSound('default');

        $notificationBuilder = new PayloadNotificationBuilder($title_noti);
        $notificationBuilder->setBody($message)
                    ->setSound('dxmb.wav');

        $dataBuilder = new PayloadDataBuilder();
        $dataBuilder->addData($data_payload);

        $option = $optionBuilder->build();
        $notification = $notificationBuilder->build();
        $data = $dataBuilder->build();


        $result = $this->getToken($user_id);

        $tokens = array_map(function($item){
            return $item['token'];
        }, $result);
//        foreach ($user_id as $item){
            $this->writeLog(
                [
                    'user_id' => $user_id,
                    'type_user' => $app_config,
                    'building_id'=>(int)$building_id,
                    'info' =>$data_payload,
                    'status'=> $this->log::STATUS_NEW, // 0 chua doc, 1 da doc
                    'read_at'=> '',
                    'see_at'=> '',
//            'message' =>$message,
//            'pay_load'=> $data_payload,
                ]
            );
//        }
        //dd($config['app_config']);

        $downstreamResponse = FCM::sendTo($tokens, $option, $notification, $data, $config['app_config']);



        //return Array - you must remove all this tokens in your database
        $this->deleteToken( $downstreamResponse->tokensToDelete() ) ;

        //return Array (key : oldToken, value : new token - you must change the token in your database )
        $this->changeToken( $downstreamResponse->tokensToModify() );

        //return Array - you should try to resend the message to the tokens in the array
         // $downstreamResponse->tokensToRetry();
//        $this->log->create(['status'=>'notify']);
//        dd($this->writeLog(
//            [
//                'message' =>$message,
//                'pay_load'=> $data_payload,
//                'status'=> $this->log::STATUS_NEW, // 0 chua doc, 1 da doc
//                'user_id' => $user_id,
//                'hide_load' =>[
//                    'numberSuccess'=>$downstreamResponse->numberSuccess(),
//                    'numberFailure'=>$downstreamResponse->numberFailure(),
//                    'numberModification'=>$downstreamResponse->numberModification(),
//                ]
//            ]
//        ));
        if ((int)$downstreamResponse->numberSuccess() > 0){
            $numberSuccess = (int)$downstreamResponse->numberFailure();
            SentStatusDetail::create([
                'sent_status_id' => $data['sent_id'],
                'contact' => $user_id,
                'status' => 'true'
            ]);
        } else                         
            SentStatusDetail::create([
                'sent_status_id' => $data['sent_id'],
                'contact' => $user_id,
                'status' => 'false'
            ]);

        return [
                    'numberSuccess'=>$downstreamResponse->numberSuccess(),
                    'numberFailure'=>$downstreamResponse->numberFailure(),
                    'numberModification'=>$downstreamResponse->numberModification(),
                ];

        // return Array (key:token, value:errror) - in production you should remove from your database the tokens

    }

    private function getToken($user_id)
    {
        return $this->modelFCM->getFcmToken($user_id);
    }

    private function writeLog($data)
    {
//        return true;
         return $this->log->create($data);
    }

    public static function setItemForQueueNotify( $data=[])
    {
       return self::queueSet(env('REDIS_QUEUE_PREFIX').'REDIS_NOTIFY_QUEUE',json_encode($data));
//        return Redis::command('rpush', [REDIS_NOTIFY_QUEUE, [ json_encode($data)]]);
    }

    public static function getItemForQueueNotify()
    {
        return json_decode( self::queuePop([env('REDIS_QUEUE_PREFIX').'REDIS_NOTIFY_QUEUE']), true);
//         return json_decode( Redis::command('lpop', [REDIS_NOTIFY_QUEUE]) );
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
        return \App\Models\Fcm::whereIn('token', $token)->delete();
    }

    private function changeToken($array)
    {
        foreach ($array as $key => $value) {
            \App\Models\Fcm::where('token', $key)->update(['token'=> $value]);
        }
        return true;
    }

}