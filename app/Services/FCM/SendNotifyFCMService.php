<?php

namespace App\Services\FCM;

use App\Commons\Util\Debug\Log as DebugLog;
use App\Helpers\dBug;
use App\Models\Campain;
use App\Models\CampainDetail;
use App\Repositories\Fcm\FcmRespository;
use App\Repositories\NotifyLog\NotifyLogRespository;
use App\Services\SendTelegram;
use LaravelFCM\Message\OptionsBuilder;
use LaravelFCM\Message\PayloadDataBuilder;
use LaravelFCM\Message\PayloadNotificationBuilder;
use Illuminate\Support\Facades\Redis;
use App\Models\Fcm as DeviceToken;
use App\Models\NotifyLog\NotifyAppLog;
use App\Models\PublicUser\UserInfo;
use App\Models\PublicUser\V2\TokenUserPush;
use App\Models\PublicUser\V2\UserInfo as V2UserInfo;
use App\Services\RedisCommanService;
use App\Util\Debug\Log;
use Exception;
use LaravelFCM\Facades\FCM;
use Carbon\Carbon;

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
    const STATUS_NEW = 0;

    private $log;
    private $modelFCM;
    public function __construct(  NotifyLogRespository $log,FcmRespository $modelFCM)
    {
        $this->log = $log;
        $this->modelFCM = $modelFCM;
    }


    public function send($prioryty = 'normal', $content_available = true, $campains)
    {
        $time_start = microtime(true);
        do {
            try {
                $data_payload = self::getItemForQueueNotify($campains->id);
                //SendTelegram::SupersendTelegramMessage('REDIS_SEND_APP_Campain'.$data_payload);
                if ($data_payload == null) {
                    //Campain::updateStatus($campains->id, 'app');
                    RedisCommanService::delKey(env('REDIS_QUEUE_PREFIX') . 'REDIS_SEND_APP_Campain_running');
                    break;
                }
                $message = $data_payload['message'];
                $title_noti = array_key_exists('title', $data_payload) ? $data_payload['title'] : 'Thông báo';
                $from_by = array_key_exists('from_by', $data_payload) ? $data_payload['from_by'] : null;
                $building_id = (int)$data_payload['building_id'];
                $user_id =  (int)$data_payload['user_id'];
                echo json_encode($data_payload).'</br>';
                $rs_campain =  Campain::find($campains->id);
                if($rs_campain){
                    $rs_campain->sended_app = $rs_campain->sended_app+1;
                    $rs_campain->update(['sended_app'=>$rs_campain->sended_app]); 
                }
                $typeCampain = config('typeCampain');
                $type_sender = 3;
                foreach ($typeCampain as $key => $value) {
                     if($value  == $rs_campain->type){
                        $type_sender = $value;
                        break;
                     }
                }
                $data_payload['type_sender']=$type_sender;
                $data_payload['id_sender']=$rs_campain->type_id;
                if($data_payload['app'] == 'v1'){
                    $userInfo = UserInfo::where(function ($query) use ($user_id, $building_id) {
                        if ($user_id) {
                            $query->where('pub_user_id', $user_id);
                        }
                        if ($building_id) {
                            $query->where('bdc_building_id', $building_id);
                        }
                    })->first();
                    if ($userInfo) {
                        try {
                            $optionBuilder = new OptionsBuilder();
                            $optionBuilder->setTimeToLive(60 * 20);
                            $optionBuilder->setPriority($prioryty);
                            $optionBuilder->setContentAvailable($content_available);
    
                            $notificationBuilder = new PayloadNotificationBuilder($title_noti);
                            $notificationBuilder->setBody($message)
                                ->setSound('sound');
    
                            $dataBuilder = new PayloadDataBuilder();
                            $dataBuilder->addData($data_payload);
    
                            $option = $optionBuilder->build();
                            $notification = $notificationBuilder->build();
                            $data = $dataBuilder->build();
    
                            //
                            $rs = $this->getToken($user_id);
                            if ($rs->count() > 0) {
                                foreach ($rs as $key => $value) {
                                    $config = [
                                        'app_config' => 'fcm.' . $value->user_type
                                    ];
                                    SendTelegram::SupersendTelegramMessage('data token:'.$value->token.':'.$option.' : '.$notification.':'.$data.' : '.$config['app_config']);
                                    $downstreamResponse = FCM::sendTo($value->token, $option, $notification, $data, $config['app_config']);
                                    if ($downstreamResponse->numberSuccess() == 1) {
                                        CampainDetail::create([
                                            'campain_id' => $data_payload['campain_id'],
                                            'type' => 'app',
                                            'building_id' => $campains->bdc_building_id,
                                            'type_campain' =>$campains->type,
                                            'view' => 0,
                                            'contact' => $userInfo->pub_user_id,
                                            'status' => true,
                                            'reason' => $userInfo->display_name,
                                            'content' => $data_payload
                                        ]);
                                        $rs_campain =  Campain::find($campains->id);
                                        if($rs_campain){
                                            $rs_campain->sended_app = $rs_campain->sended_app+1;
                                            $rs_campain->update(['sended_app'=>$rs_campain->sended_app]); 
                                            $total = json_decode($rs_campain->total);
                                            if ($rs_campain->sended_app >= $total->app) {
                                                Campain::updateStatus($rs_campain->id, 'app');
                                            }
                                        }
                                        SendTelegram::SupersendTelegramMessage('Done push noti: '.$data_payload['campain_id'].' : '.json_encode($downstreamResponse));
                                    } else {
                                        SendTelegram::SupersendTelegramMessage('False push noti: '.$data_payload['campain_id'].' : '.json_encode($downstreamResponse));
                                        CampainDetail::create([
                                            'campain_id' => $data_payload['campain_id'],
                                            'type' => 'app',
                                            'building_id' => $campains->bdc_building_id,
                                            'type_campain' =>$campains->type,
                                            'view' => 0,
                                            'contact' => $userInfo->pub_user_id,
                                            'status' => false,
                                            'reason' => json_encode($downstreamResponse),
                                            'content' => $data_payload
                                        ]);
                                    }
                                }
    
                                return [
                                    'numberSuccess' => $downstreamResponse->numberSuccess(),
                                    'numberFailure' => $downstreamResponse->numberFailure(),
                                    'numberModification' => $downstreamResponse->numberModification(),
                                ];
                            }
                        } catch (\Exception $e) {
    
                            CampainDetail::create([
                                'campain_id' => $data_payload['campain_id'],
                                'type' => 'app',
                                'building_id' => $campains->bdc_building_id,
                                'type_campain' =>$campains->type,
                                'view' => 0,
                                'contact' => $userInfo->pub_user_id,
                                'status' => false,
                                'reason' => $e->getMessage(),
                                'content' => $data_payload,
                            ]);
                        }
                    }
                }
                if($data_payload['app'] == 'v2')
                {
                    // DebugLog::info('check_notify_new','1_'.json_encode($data_payload));
                    $userInfo = V2UserInfo::where('user_id',$user_id)->first();
                    // dBug::trackingPhpErrorV2($userInfo);
                    if ($userInfo) {
                        try {

                            $optionBuilder = new OptionsBuilder();
                            $optionBuilder->setTimeToLive(60 * 20);
                            $optionBuilder->setPriority($prioryty);
                            $optionBuilder->setContentAvailable($content_available);
    
                            $notificationBuilder = new PayloadNotificationBuilder($title_noti);
                            $notificationBuilder->setBody($message)
                                ->setSound('sound');
    
                            $dataBuilder = new PayloadDataBuilder();
                            $dataBuilder->addData($data_payload);
    
                            $option = $optionBuilder->build();
                            $notification = $notificationBuilder->build();
                            $data = $dataBuilder->build();
    
                            //
                            $rs =  TokenUserPush::where('user_id', $userInfo->user_id)->get();
                            if ($rs->count() > 0) {
                                foreach ($rs as $key => $value) {
                                   // dBug::trackingPhpErrorV2($value);
                                    $config_type = $value->bundle_id == 'com.portalbeanz.ash' ||  $value->bundle_id == 'com.asahi.bdc' ? 'asahi' : 'cudan';
                                    $config_type = $value->bundle_id == 'com.dxmb.bms' ? 'banquanly' : $config_type;
                                    $config = [
                                        'app_config' => 'fcm.' . $config_type
                                    ];
                                    $downstreamResponse = FCM::sendTo($value->token, $option, $notification, $data, $config['app_config']);
                                    if ($downstreamResponse->numberSuccess() == 1) {
                                        CampainDetail::create([
                                            'campain_id' => $data_payload['campain_id'],
                                            'type' => 'app',
                                            'building_id' => $campains->bdc_building_id,
                                            'type_campain' =>$campains->type,
                                            'view' => 0,
                                            'contact' => $userInfo->user_id,
                                            'status' => true,
                                            'reason' => $userInfo->full_name,
                                            'content' => $data_payload
                                        ]);
                                        $rs_campain =  Campain::find($campains->id);
                                        if($rs_campain){
                                            $rs_campain->sended_app = $rs_campain->sended_app+1;
                                            $rs_campain->update(['sended_app'=>$rs_campain->sended_app]); 
                                            $total = json_decode($rs_campain->total);
                                            if ($rs_campain->sended_app >= $total->app) {
                                                Campain::updateStatus($rs_campain->id, 'app');
                                            }
                                        }
                                      
                                    } else {
                                        CampainDetail::create([
                                            'campain_id' => $data_payload['campain_id'],
                                            'type' => 'app',
                                            'building_id' => $campains->bdc_building_id,
                                            'type_campain' =>$campains->type,
                                            'view' => 0,
                                            'contact' => $userInfo->user_id,
                                            'status' => false,
                                            'reason' => json_encode($downstreamResponse),
                                            'content' => $data_payload
                                        ]);
                                    }
                                }
                                $notify = [
                                    'type_sender' => $type_sender,
                                    'id_sender' => $rs_campain->type_id,
                                    'user_id' => $userInfo->user_id,
                                    'by' => 0,
                                    'from_by' => @$from_by,
                                    'building_id' => $campains->bdc_building_id,
                                    'content' => $data_payload['message'],
                                    'action_desc' => $title_noti, 
                                    'status' => 0,
                                    'type_request'=>  array_key_exists('type_request', $data_payload) ? $data_payload['type_request'] : -1
                                ];
                                NotifyAppLog::create($notify);
                                return [
                                    'numberSuccess' => $downstreamResponse->numberSuccess(),
                                    'numberFailure' => $downstreamResponse->numberFailure(),
                                    'numberModification' => $downstreamResponse->numberModification(),
                                ];
                            }
                        } catch (\Exception $e) {
                            // dBug::trackingPhpErrorV2( $e->getMessage());
                            // DebugLog::info('check_notify_new','2_'.$e->getTraceAsString());
                            CampainDetail::create([
                                'campain_id' => $data_payload['campain_id'],
                                'type' => 'app',
                                'building_id' => $campains->bdc_building_id,
                                'type_campain' =>$campains->type,
                                'view' => 0,
                                'contact' => $userInfo->user_id,
                                'status' => false,
                                'reason' => $e->getMessage(),
                                'content' => $data_payload,
                            ]);
                        }
                    } 
                   
                }
                $time_end = microtime(true);
                $time = $time_end - $time_start;
            } catch (Exception $e) {
               echo $e->getMessage();
               break;
            }
        } while ($data_payload || $time < 57);

    }

    public static function sendV2($prioryty = 'normal', $content_available = true)
    {
        $time_start = microtime(true);
        do {
            try {
                $allKey = Redis::keys('*REDIS_SEND_APP_Campain_*');
                $data_payload =  json_decode( self::queuePop([@$allKey[0]]), true);
                if ($data_payload) {
                    $array = explode('_', $allKey[0]);
                    $campain_id = $array[count($array)-1];

                    $message = $data_payload['message'];
                    $title_noti = array_key_exists('title', $data_payload) ? $data_payload['title'] : 'Thông báo';
                    $from_by = array_key_exists('from_by', $data_payload) ? $data_payload['from_by'] : null;
                    $building_id = (int)$data_payload['building_id'];
                    $user_id =  (int)$data_payload['user_id'];
                    echo json_encode($data_payload).'</br>';
                    $rs_campain =  Campain::find($campain_id);
                    if($rs_campain){
                        $rs_campain->sended_app = $rs_campain->sended_app+1;
                        $rs_campain->update(['sended_app'=>$rs_campain->sended_app]); 
                    }
                    $typeCampain = config('typeCampain');
                    $type_sender = 3;
                    foreach ($typeCampain as $key => $value) {
                         if($value  == $rs_campain->type){
                            $type_sender = $value;
                            break;
                         }
                    }
                    $data_payload['type_sender']=$type_sender;
                    $data_payload['id_sender']=$rs_campain->type_id;
                    if($data_payload['app'] == 'v1'){
                        $userInfo = UserInfo::where(function ($query) use ($user_id, $building_id) {
                            if ($user_id) {
                                $query->where('pub_user_id', $user_id);
                            }
                            if ($building_id) {
                                $query->where('bdc_building_id', $building_id);
                            }
                        })->first();
                        if ($userInfo) {
                            try {
                                $optionBuilder = new OptionsBuilder();
                                $optionBuilder->setTimeToLive(60 * 20);
                                $optionBuilder->setPriority($prioryty);
                                $optionBuilder->setContentAvailable($content_available);
        
                                $notificationBuilder = new PayloadNotificationBuilder($title_noti);
                                $notificationBuilder->setBody($message)
                                    ->setSound('sound');
        
                                $dataBuilder = new PayloadDataBuilder();
                                $dataBuilder->addData(['data' => json_encode($data_payload)]);
        
                                $option = $optionBuilder->build();
                                $notification = $notificationBuilder->build();
                                $data = $dataBuilder->build();
        
                                //
                                $rs = self::getToken($user_id);
                                if ($rs->count() > 0) {
                                    foreach ($rs as $key => $value) {
                                        $config = [
                                            'app_config' => 'fcm.' . $value->user_type
                                        ];
                                        $downstreamResponse = FCM::sendTo($value->token, $option, $notification, $data, $config['app_config']);
                                  
                                    }
        
                                }
                            } catch (\Exception $e) {
                                SendTelegram::SupersendTelegramMessage('sendnotify V2:'.json_encode($e->getMessage()));
                            }
                        }
                    }
                    if($data_payload['app'] == 'v2')
                    {
                        $userInfo = V2UserInfo::where('user_id',$user_id)->first();
                       // dBug::trackingPhpErrorV2($userInfo);
                        if ($userInfo) {
                            try {
        
                                $optionBuilder = new OptionsBuilder();
                                $optionBuilder->setTimeToLive(60 * 20);
                                $optionBuilder->setPriority($prioryty);
                                $optionBuilder->setContentAvailable($content_available);
        
                                $notificationBuilder = new PayloadNotificationBuilder($title_noti);
                                $notificationBuilder->setBody($message)
                                    ->setSound('sound');
        
                                $dataBuilder = new PayloadDataBuilder();
                                $dataBuilder->addData(['data' => json_encode($data_payload)]);
        
                                $option = $optionBuilder->build();
                                $notification = $notificationBuilder->build();
                                $data = $dataBuilder->build();
        
                                //
                                $rs =  TokenUserPush::where('user_id', $userInfo->user_id)->get();
                                if ($rs->count() > 0) {
                                    foreach ($rs as $key => $value) {
                                       // dBug::trackingPhpErrorV2($value);
                                        $config_type = $value->bundle_id == 'com.portalbeanz.ash' ||  $value->bundle_id == 'com.asahi.bdc' ? 'asahi' : 'cudan';
                                        $config_type = $value->bundle_id == 'com.dxmb.bms' ? 'banquanly' : $config_type;
                                        $config = [
                                            'app_config' => 'fcm.' . $config_type
                                        ];
                                        $downstreamResponse = FCM::sendTo($value->token, $option, $notification, $data, $config['app_config']);
                                        if ($downstreamResponse->numberSuccess() == 1) {
                                            CampainDetail::create([
                                                'campain_id' => $data_payload['campain_id'],
                                                'type' => 'app',
                                                'building_id' => $rs_campain->bdc_building_id,
                                                'type_campain' =>$rs_campain->type,
                                                'view' => 0,
                                                'contact' => $userInfo->user_id,
                                                'status' => true,
                                                'reason' => $userInfo->full_name,
                                                'content' => $data_payload
                                            ]);
                                           
                                          
                                        } else {
                                            CampainDetail::create([
                                                'campain_id' => $data_payload['campain_id'],
                                                'type' => 'app',
                                                'building_id' => $rs_campain->bdc_building_id,
                                                'type_campain' =>$rs_campain->type,
                                                'view' => 0,
                                                'contact' => $userInfo->user_id,
                                                'status' => false,
                                                'reason' => json_encode($downstreamResponse),
                                                'content' => $data_payload
                                            ]);
                                        }
                                    }
                                    $notify = [
                                        'type_sender' => $type_sender,
                                        'id_sender' => $rs_campain->type_id,
                                        'user_id' => $userInfo->user_id,
                                        'by' => 0,
                                        'from_by' => @$from_by,
                                        'building_id' => $building_id,
                                        'content' => $data_payload['message'],
                                        'action_desc' => $title_noti, 
                                        'status' => 0,
                                        'type_request'=>  array_key_exists('type_request', $data_payload) ? $data_payload['type_request'] : -1
                                    ];
                                    NotifyAppLog::create($notify);
                                }
                            } catch (\Exception $e) {
                                SendTelegram::SupersendTelegramMessage('sendnotify V2 455:'.json_encode($e->getMessage()));
                            }
                        } 
                       
                    }
                }
                //end if 
               
                $time_end = microtime(true);
                $time = $time_end - $time_start;
            } catch (Exception $e) {
               echo $e->getMessage();
               continue;
            }
        } while ($time < 120);
        echo 'This command loaded in ', $time, ' seconds';  
    }
    public static function sendV3($prioryty = 'normal', $content_available = true)
    {
        $time_start = microtime(true);
        do {
            try {
                $allKey = Redis::keys('*REDIS_SEND_APP_Campain_[1-9][1-9][2-9][0-9][0-9][0-9]*');
                $data_payload =  json_decode( self::queuePop([@$allKey[0]]), true);
                if ($data_payload) {

                    $array = explode('_', $allKey[0]);
                    $campain_id = $array[count($array)-1];
                    $message = $data_payload['message'];
                    $title_noti = array_key_exists('title', $data_payload) ? $data_payload['title'] : 'Thông báo';
                    $from_by = array_key_exists('from_by', $data_payload) ? $data_payload['from_by'] : null;
                    $building_id = (int)$data_payload['building_id'];
                    $user_id =  (int)$data_payload['user_id'];
                    echo json_encode($data_payload).'</br>';
                    $rs_campain =  Campain::find($campain_id);
                    if($rs_campain){
                        $rs_campain->sended_app = $rs_campain->sended_app+1;
                        $rs_campain->update(['sended_app'=>$rs_campain->sended_app]); 
                    }
                    $typeCampain = config('typeCampain');
                    $type_sender = 3;
                    foreach ($typeCampain as $key => $value) {
                         if($value  == $rs_campain->type){
                            $type_sender = $value;
                            break;
                         }
                    }
                    $data_payload['type_sender']=$type_sender;
                    $data_payload['id_sender']=$rs_campain->type_id;
                    if($data_payload['app'] == 'v1'){
                        $userInfo = UserInfo::where(function ($query) use ($user_id, $building_id) {
                            if ($user_id) {
                                $query->where('pub_user_id', $user_id);
                            }
                            if ($building_id) {
                                $query->where('bdc_building_id', $building_id);
                            }
                        })->first();
                        if ($userInfo) {
                            try {
                                $optionBuilder = new OptionsBuilder();
                                $optionBuilder->setTimeToLive(60 * 20);
                                $optionBuilder->setPriority($prioryty);
                                $optionBuilder->setContentAvailable($content_available);
        
                                $notificationBuilder = new PayloadNotificationBuilder($title_noti);
                                $notificationBuilder->setBody($message)
                                    ->setSound('sound');
        
                                $dataBuilder = new PayloadDataBuilder();
                                $dataBuilder->addData(['data' => json_encode($data_payload)]);
        
                                $option = $optionBuilder->build();
                                $notification = $notificationBuilder->build();
                                $data = $dataBuilder->build();
        
                                //
                                $rs = self::getToken($user_id);
                                if ($rs->count() > 0) {
                                    foreach ($rs as $key => $value) {
                                        $config = [
                                            'app_config' => 'fcm.' . $value->user_type
                                        ];
                                        $downstreamResponse = FCM::sendTo($value->token, $option, $notification, $data, $config['app_config']);
                                  
                                    }
        
                                }
                            } catch (\Exception $e) {
                                SendTelegram::SupersendTelegramMessage('sendnotify V2:'.json_encode($e->getMessage()));
                            }
                        }
                    }
                    if($data_payload['app'] == 'v2')
                    {
                        $userInfo = V2UserInfo::where('user_id',$user_id)->first();
                       // dBug::trackingPhpErrorV2($userInfo);
                        if ($userInfo) {
                            try {
        
                                $optionBuilder = new OptionsBuilder();
                                $optionBuilder->setTimeToLive(60 * 20);
                                $optionBuilder->setPriority($prioryty);
                                $optionBuilder->setContentAvailable($content_available);
        
                                $notificationBuilder = new PayloadNotificationBuilder($title_noti);
                                $notificationBuilder->setBody($message)
                                    ->setSound('sound');
        
                                $dataBuilder = new PayloadDataBuilder();
                                $dataBuilder->addData(['data' => json_encode($data_payload)]);
        
                                $option = $optionBuilder->build();
                                $notification = $notificationBuilder->build();
                                $data = $dataBuilder->build();
        
                                //
                                $rs =  TokenUserPush::where('user_id', $userInfo->user_id)->get();
                                if ($rs->count() > 0) {
                                    foreach ($rs as $key => $value) {
                                       // dBug::trackingPhpErrorV2($value);
                                        $config_type = $value->bundle_id == 'com.portalbeanz.ash' ||  $value->bundle_id == 'com.asahi.bdc' ? 'asahi' : 'cudan';
                                        $config_type = $value->bundle_id == 'com.dxmb.bms' ? 'banquanly' : $config_type;
                                        $config = [
                                            'app_config' => 'fcm.' . $config_type
                                        ];
                                        $downstreamResponse = FCM::sendTo($value->token, $option, $notification, $data, $config['app_config']);
                                        if ($downstreamResponse->numberSuccess() == 1) {
                                            CampainDetail::create([
                                                'campain_id' => $data_payload['campain_id'],
                                                'type' => 'app',
                                                'building_id' => $rs_campain->bdc_building_id,
                                                'type_campain' =>$rs_campain->type,
                                                'view' => 0,
                                                'contact' => $userInfo->user_id,
                                                'status' => true,
                                                'reason' => $userInfo->full_name,
                                                'content' => $data_payload
                                            ]);
                                           
                                          
                                        } else {
                                            CampainDetail::create([
                                                'campain_id' => $data_payload['campain_id'],
                                                'type' => 'app',
                                                'building_id' => $rs_campain->bdc_building_id,
                                                'type_campain' =>$rs_campain->type,
                                                'view' => 0,
                                                'contact' => $userInfo->user_id,
                                                'status' => false,
                                                'reason' => json_encode($downstreamResponse),
                                                'content' => $data_payload
                                            ]);
                                        }
                                    }
                                    $notify = [
                                        'type_sender' => $type_sender,
                                        'id_sender' => $rs_campain->type_id,
                                        'user_id' => $userInfo->user_id,
                                        'by' => 0,
                                        'from_by' => @$from_by,
                                        'building_id' => $building_id,
                                        'content' => $data_payload['message'],
                                        'action_desc' => $title_noti, 
                                        'status' => 0,
                                        'type_request'=>  array_key_exists('type_request', $data_payload) ? $data_payload['type_request'] : -1
                                    ];
                                    NotifyAppLog::create($notify);
                                }
                            } catch (\Exception $e) {
                                SendTelegram::SupersendTelegramMessage('sendnotify V2 455:'.json_encode($e->getMessage()));
                            }
                        } 
                       
                    }
                }
                //end if 
               
                $time_end = microtime(true);
                $time = $time_end - $time_start;
            } catch (Exception $e) {
               echo $e->getMessage();
               continue;
            }
        } while ($time < 120);
        echo 'This command loaded in ', $time, ' seconds';  
    }
    public static function sendV4($prioryty = 'normal', $content_available = true)
    {
        $time_start = microtime(true);
        do {
            try {
                $allKey = Redis::keys('*REDIS_SEND_APP_Campain_105299*');
                $data_payload =  json_decode( self::queuePop([@$allKey[0]]), true);
                if ($data_payload) {

                    $array = explode('_', $allKey[0]);
                    $campain_id = $array[count($array)-1];
                    $message = $data_payload['message'];
                    $title_noti = array_key_exists('title', $data_payload) ? $data_payload['title'] : 'Thông báo';
                    $from_by = array_key_exists('from_by', $data_payload) ? $data_payload['from_by'] : null;
                    $building_id = (int)$data_payload['building_id'];
                    $user_id =  (int)$data_payload['user_id'];
                    echo json_encode($data_payload).'</br>';
                    $rs_campain =  Campain::find($campain_id);
                    if($rs_campain){
                        $rs_campain->sended_app = $rs_campain->sended_app+1;
                        $rs_campain->update(['sended_app'=>$rs_campain->sended_app]); 
                    }
                    $typeCampain = config('typeCampain');
                    $type_sender = 3;
                    foreach ($typeCampain as $key => $value) {
                         if($value  == $rs_campain->type){
                            $type_sender = $value;
                            break;
                         }
                    }
                    $data_payload['type_sender']=$type_sender;
                    $data_payload['id_sender']=$rs_campain->type_id;
                    if($data_payload['app'] == 'v1'){
                        $userInfo = UserInfo::where(function ($query) use ($user_id, $building_id) {
                            if ($user_id) {
                                $query->where('pub_user_id', $user_id);
                            }
                            if ($building_id) {
                                $query->where('bdc_building_id', $building_id);
                            }
                        })->first();
                        if ($userInfo) {
                            try {
                                $optionBuilder = new OptionsBuilder();
                                $optionBuilder->setTimeToLive(60 * 20);
                                $optionBuilder->setPriority($prioryty);
                                $optionBuilder->setContentAvailable($content_available);
        
                                $notificationBuilder = new PayloadNotificationBuilder($title_noti);
                                $notificationBuilder->setBody($message)
                                    ->setSound('sound');
        
                                $dataBuilder = new PayloadDataBuilder();
                                $dataBuilder->addData(['data' => json_encode($data_payload)]);
        
                                $option = $optionBuilder->build();
                                $notification = $notificationBuilder->build();
                                $data = $dataBuilder->build();
        
                                //
                                $rs = self::getToken($user_id);
                                if ($rs->count() > 0) {
                                    foreach ($rs as $key => $value) {
                                        $config = [
                                            'app_config' => 'fcm.' . $value->user_type
                                        ];
                                        $downstreamResponse = FCM::sendTo($value->token, $option, $notification, $data, $config['app_config']);
                                  
                                    }
        
                                }
                            } catch (\Exception $e) {
                                SendTelegram::SupersendTelegramMessage('sendnotify V2:'.json_encode($e->getMessage()));
                            }
                        }
                    }
                    if($data_payload['app'] == 'v2')
                    {
                        $userInfo = V2UserInfo::where('user_id',$user_id)->first();
                       // dBug::trackingPhpErrorV2($userInfo);
                        if ($userInfo) {
                            try {
        
                                $optionBuilder = new OptionsBuilder();
                                $optionBuilder->setTimeToLive(60 * 20);
                                $optionBuilder->setPriority($prioryty);
                                $optionBuilder->setContentAvailable($content_available);
        
                                $notificationBuilder = new PayloadNotificationBuilder($title_noti);
                                $notificationBuilder->setBody($message)
                                    ->setSound('sound');
        
                                $dataBuilder = new PayloadDataBuilder();
                                $dataBuilder->addData(['data' => json_encode($data_payload)]);
        
                                $option = $optionBuilder->build();
                                $notification = $notificationBuilder->build();
                                $data = $dataBuilder->build();
        
                                //
                                $rs =  TokenUserPush::where('user_id', $userInfo->user_id)->get();
                                if ($rs->count() > 0) {
                                    foreach ($rs as $key => $value) {
                                       // dBug::trackingPhpErrorV2($value);
                                        $config_type = $value->bundle_id == 'com.portalbeanz.ash' ||  $value->bundle_id == 'com.asahi.bdc' ? 'asahi' : 'cudan';
                                        $config_type = $value->bundle_id == 'com.dxmb.bms' ? 'banquanly' : $config_type;
                                        $config = [
                                            'app_config' => 'fcm.' . $config_type
                                        ];
                                        $downstreamResponse = FCM::sendTo($value->token, $option, $notification, $data, $config['app_config']);
                                        if ($downstreamResponse->numberSuccess() == 1) {
                                            CampainDetail::create([
                                                'campain_id' => $data_payload['campain_id'],
                                                'type' => 'app',
                                                'building_id' => $rs_campain->bdc_building_id,
                                                'type_campain' =>$rs_campain->type,
                                                'view' => 0,
                                                'contact' => $userInfo->user_id,
                                                'status' => true,
                                                'reason' => $userInfo->full_name,
                                                'content' => $data_payload
                                            ]);
                                           
                                          
                                        } else {
                                            CampainDetail::create([
                                                'campain_id' => $data_payload['campain_id'],
                                                'type' => 'app',
                                                'building_id' => $rs_campain->bdc_building_id,
                                                'type_campain' =>$rs_campain->type,
                                                'view' => 0,
                                                'contact' => $userInfo->user_id,
                                                'status' => false,
                                                'reason' => json_encode($downstreamResponse),
                                                'content' => $data_payload
                                            ]);
                                        }
                                    }
                                    $notify = [
                                        'type_sender' => $type_sender,
                                        'id_sender' => $rs_campain->type_id,
                                        'user_id' => $userInfo->user_id,
                                        'by' => 0,
                                        'from_by' => @$from_by,
                                        'building_id' => $building_id,
                                        'content' => $data_payload['message'],
                                        'action_desc' => $title_noti, 
                                        'status' => 0,
                                        'type_request'=>  array_key_exists('type_request', $data_payload) ? $data_payload['type_request'] : -1
                                    ];
                                    NotifyAppLog::create($notify);
                                }
                            } catch (\Exception $e) {
                                SendTelegram::SupersendTelegramMessage('sendnotify V2 455:'.json_encode($e->getMessage()));
                            }
                        } 
                       
                    }
                }
                //end if 
               
                $time_end = microtime(true);
                $time = $time_end - $time_start;
            } catch (Exception $e) {
               echo $e->getMessage();
               continue;
            }
        } while ($time < 120);
        echo 'This command loaded in ', $time, ' seconds';  
    }


    public static function testPush($token, $message = 'chi la demo thoi ma 3', $data_payload = ['something' => 'payload demo'], $type_config, $tile = 'Thông báo')
    {
        try {

            $optionBuilder = new OptionsBuilder();
            $optionBuilder->setTimeToLive(60*20);
            $optionBuilder->setPriority('normal');
            $optionBuilder->setContentAvailable('');

            $notificationBuilder = new PayloadNotificationBuilder($tile);
            $notificationBuilder->setBody($message)
                ->setSound('sound');

            $dataBuilder = new PayloadDataBuilder();
            $dataBuilder->addData(['data'=>json_encode($data_payload)] );

            $option = $optionBuilder->build();
            $notification = $notificationBuilder->build();
            $data = $dataBuilder->build();
            $config=[
                'app_config'=> 'fcm.'.$type_config
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


    public static function pushNotify($token, $data_payload = ['something' => 'payload demo'], $type_config )
    {
        try {

            $optionBuilder = new OptionsBuilder();
            $optionBuilder->setTimeToLive(60*20);
            $optionBuilder->setPriority('normal');
            $optionBuilder->setContentAvailable('');

            $notificationBuilder = new PayloadNotificationBuilder($data_payload['title']);
            $notificationBuilder->setBody($data_payload['message'])
                ->setSound('sound');

            $dataBuilder = new PayloadDataBuilder();
            $dataBuilder->addData(['data'=>json_encode($data_payload)] );

            $option = $optionBuilder->build();
            $notification = $notificationBuilder->build();
            $data = $dataBuilder->build();

            $config_type = $type_config == 'com.portalbeanz.ash' || $type_config == 'com.asahi.bdc' ? 'asahi' : 'cudan';

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


    private function getToken($user_id)
    {
        SendTelegram::SupersendTelegramMessage('token'.$this->modelFCM->getFcmToken($user_id));
        return $this->modelFCM->getFcmToken($user_id);
    }

    private function writeLog($data)
    {
        return $this->log->create($data);
    }

    public static function setItemForQueueNotify( $data=[])
    {
        //SendTelegram::SupersendTelegramMessage('setItemForQueueNotify:'.json_encode($data));
       return self::queueSet(env('REDIS_QUEUE_PREFIX'). 'REDIS_SEND_APP_Campain_' . $data['campain_id'],json_encode($data));
    }

    public static function getItemForQueueNotify($id)
    {
        $rs = json_decode(self::queuePop([env('REDIS_QUEUE_PREFIX') . 'REDIS_SEND_APP_Campain_'.$id]), true);

        if($rs){
            //  đang gửi app
            RedisCommanService::setKey(env('REDIS_QUEUE_PREFIX') . 'REDIS_SEND_APP_Campain_running', json_encode(['id'=>$id, 'started_at'=>strtotime(now())]));
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