<?php

namespace App\Services;

use Illuminate\Support\Facades\Redis;
use App\Models\LogSendSMS\LogSendSMS;

class SendSMSSoap
{
    public static function setItemForQueue( $data=[])
    {
        return self::queueSet(env('REDIS_QUEUE_PREFIX').'SEVICE_SEND_SMS',json_encode($data));
    }

    public static function getItemForQueue()
    {
        return json_decode( self::queuePop([env('REDIS_QUEUE_PREFIX').'SEVICE_SEND_SMS']), true);
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
    public static function sendSMS($content = '', $target='', $api_gate=true) {
        try{
            if (!$content) {
                LogSendSMS::create([
                    'content'=> $content,
                    'target' => $target,
                    'api_gate' => $api_gate,
                    'error' => 404,
                    'message'=>'[sendSMS] Chưa có nội dung gửi tới SĐT: '.$target,
                ]);

                return false;
            }
            if (!$target) return false;

            $rs = SendSMSSoap::checkLogLastSend([
                'content'=> $content,
                'target' => $target,
                'api_gate' => $api_gate
            ]);

            if ($rs) {
                 LogSendSMS::create([
                    'content'=> $content,
                    'target' => $target,
                    'api_gate' => $api_gate,
                    'error' => 300,
                    'message'=>'[sendSMS] Có nội dung giống nhau tới SĐT: '.$target . '. id :'.$rs->id
                ]);
            }

            $target = SendSMSSoap::formatPhoneVN((string) $target);
            $payload = [
                "destination"   => $target,
                "sender"        => env('BRAND_NAME'),
                "keyword"       => "DXMB",
                "outContent"    => trim($content),
                "chargingFlag"  => "0",
                "moSeqNo"       => "0",
                "contentType"   => "0",
                "localTime"     => now()->format("YmdHis").'000',
                "userName"      => env('BRAND_USER'),
                "password"      => env('BRAND_PASSWORD')
            ];
            /** @var $client */
            $client = new \SoapClient(env('BRAND_WSDL'));
            /** @var $objResponse */
            $objResponse = $client->sendMT($payload);
            /** Log SMS */
            LogSendSMS::create([
                'content'=> $content,
                'target' => $target,
                'api_gate' => $api_gate,
                'error' => 200,
                'message'=>'[sendSMS] done',
                'response'=>json_encode($objResponse)
            ]);
            // LogSendSMS::debug('[sendSMS] Đã gửi sms tới '.$target, $payload);
            // $sms_log = new SMSLog();
            // $sms_log->target = $target;
            // $sms_log->content = $content;
            // $sms_log->response = json_encode($objResponse);
            // $sms_log->save();
            // LogController::logCustomer('[sendSMS] Đã gửi sms tới '.$target, 'notice', null, true, $api_gate);
            if ($objResponse && starts_with($objResponse->return, '00')) return true;
            return false;
        }catch(\Exception $e){
            \Log::channel('single')->info($e->getMessage() . PHP_EOL);
            LogSendSMS::create([
                'content'=> $content,
                'target' => $target,
                'api_gate' => $api_gate,
                'error' => $e->getCode(),
                'message'=>$e->getMessage(),
                'response'=>json_encode($objResponse)
            ]);
            return false;
        }
    }

    public static function checkLogLastSend($data)
    {
        // LogSendSMS::where([])
        return false;
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