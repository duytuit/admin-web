<?php

namespace App\Services;

use App\Helpers\dBug;
use Illuminate\Support\Facades\Redis;
use App\Models\LogSendSMS\LogSendSMS;
use App\Models\Building\Building;
use App\Models\Campain;
use App\Models\CampainDetail;
use Carbon\Carbon;

class SendSMSSoapV2
{
    const NEW_USER ='sms_new_user';
    const FORGOT ='sms_forgot';
    const LOGIN_SMS ='sms_login';
    const LOGIN_FORGOT ='sms_forgot';
    const APARTMENT_HANDOVER = 'sms_apartment_handover';
    const APARTMENT_HANDOVER_CUSTOM = 'sms_apartment_handover_custom';
    const POST_CONTENT = 'sms_post';
    const ASSET_HANDOVER = 'sms_asset_handover';
    const TRANSACTION_PAYMENT = 'sms_transaction_payment';
    public static function setItemForQueue( $data=[])
    {
        //return self::queueSet(env('REDIS_QUEUE_PREFIX').'SEVICE_SEND_SMS',json_encode($data));
        return self::queueSet(env('REDIS_QUEUE_PREFIX') . 'REDIS_SEND_SMS_Campain_' . $data['campain_id'], json_encode($data));
    }

    public static function getItemForQueue()
    {
        return json_decode( self::queuePop([env('REDIS_QUEUE_PREFIX').'SEVICE_SEND_SMS']), true);
    }

     // Lấy/pop sms từ hàng đợi campain
     public static function getItemForQueueV2($id)
     {
         $rs = json_decode( self::queuePop([env('REDIS_QUEUE_PREFIX').'REDIS_SEND_SMS_Campain_'. $id]), true);
         if($rs){
            RedisCommanService::setKey(env('REDIS_QUEUE_PREFIX') . 'REDIS_SEND_SMS_Campain_running', json_encode(['id'=>$id, 'started_at'=>strtotime(now())]));
         }
         return $rs;
     }

    public static function queueSet($key, $data)
    {
        return Redis::command('rpush', [$key, [$data]]);
    }

    public static function queuePop($key=[])
    {
        return Redis::command('lpop', $key);
    }

    /**
     * @param string $content
     * @param string $target
     * @param bool $api_gate
     * @return bool
     */
    public static function sendSMS($content, $target, $building_id, $type, $type_app = null, $campains = null)
    {

        try{
            $building = Building::get_detail_building_by_building_id($building_id);
            $client = new \GuzzleHttp\Client();
            if($type_app){
                $array_send_sms = [
                    'phone' => $target,
                    'message' =>  json_encode($content),
                    'code' => $type_app . '_' . $type,
                    'building_id' => @$building->id
                ];
            }else{
                $array_send_sms=[
                    'phone'=> $target,
                    'message'=>  json_encode($content),
                    'code'=> $building->template_mail.'_'.$type,
                    'building_id' => @$building->id
                ];
                if($type == self::APARTMENT_HANDOVER_CUSTOM){
                    $data_array = $content['content'];
                    if (isset($content['params'])) {
                        $content_param = (object)$content['params'];
                        foreach ($content_param as $key => $value) {
                            $data_array = str_replace($key, $value, $data_array);
                        }
                    }
                    $array_send_sms=[
                         'phone'=> $target,
                         'message'=> $data_array,
                         'code'=> $building->template_mail.'_'.self::APARTMENT_HANDOVER,
                         'building_id' => @$building->id
                    ];
                }
            }
            $headers = [
                'ClientSecret' =>env('ClientSecret_bdc'),
                'ClientId' => env('ClientId_bdc'),
            ];
          
            $responseReource = $client->request('POST','http://authv2.dxmb.vn/api/v2/notification/sendSms', [
                'headers' => $headers,
                'json' => $array_send_sms,
            ]);
            $result_resource = json_decode((string) $responseReource->getBody(), true);

            if($campains){
                CampainDetail::create([
                    'campain_id' => $campains->id,
                    'type' => 'sms',
                    'building_id' => $campains->bdc_building_id,
                    'type_campain' =>$campains->type,
                    'view' => 0,
                    'contact' => $target,
                    'status' => $result_resource['success'],
                    'reason' => $result_resource['message'],
                    'content' => isset($array_send_sms) ? $array_send_sms : null,
                ]);
                $rs_campain =  Campain::find($campains->id);
                if($rs_campain){
                    $rs_campain->sended_sms = $rs_campain->sended_sms+1;
                    $rs_campain->update(['sended_sms'=>$rs_campain->sended_sms]); 
                }
            }

            LogSendSMS::create([
                'building_id'=> $building_id,
                'contents'=> json_encode($content),
                'target' => $target,
                'error' => 200,
                'message'=>'[sendSMS] Thành công_'.$building->template_mail,
                'response'=>isset($result_resource) ? json_encode($result_resource) : null,
                'created_date' => Carbon::now()->toDateTimeString()
            ]);

            return true;

        }catch(\Exception $e){
            LogSendSMS::create([
                'building_id'=> $building_id,
                'contents'=> json_encode($content),
                'target' => $target,
                'error' => $e->getCode(),
                'message'=>$e->getMessage(),
                'response'=>isset($result_resource) ? json_encode($result_resource) : null,
                'created_date' => Carbon::now()->toDateTimeString()
            ]);

            if($campains){
                CampainDetail::create([
                    'campain_id' => $campains->id,
                    'type' => 'sms',
                    'building_id' => $campains->bdc_building_id,
                    'type_campain' =>$campains->type,
                    'view' => 0,
                    'contact' => $target,
                    'status' => false,
                    'reason' => $e->getMessage(),
                    'content' => json_encode($content),
                ]);
            }
            return false;
        }
    }

    public static function checkLogLastSend($data)
    {
        return LogSendSMS::where(['content'=>$data['content'],'target'=>$data['target']])->first() == null ? false : true;
    }

    public static function formatPhoneVN($phone = '') {
        $phone = str_replace(' ', '', $phone);
        $phone = preg_replace('/[\x00-\x1F\x7f-\xFF]/', '', $phone);
        if (starts_with($phone, '0')) {
            $phone = '84' . substr($phone, 1);
        }
        return $phone;
    }


}