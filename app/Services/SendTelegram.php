<?php

namespace App\Services;
class SendTelegram
{
    public static function sendTelegramMessage( $data )
    {

        $telegramApiKey = '5175382144:AAEFgtXE4DDkGigkt7-x8yV9ahn3EAmgb-4';
        $chatId = '-926194946';
        $message = json_encode($data);;

        // Tạo payload cho yêu cầu
        $payload = [
            'chat_id' => $chatId,
            'text' => $message,
        ];
        // Chuyển đổi payload thành định dạng JSON
        $jsonData = json_encode($payload);
        // Tạo cURL request
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, "https://api.telegram.org/bot{$telegramApiKey}/sendMessage");
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $jsonData);
        curl_setopt($curl, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        // Gửi yêu cầu và lấy kết quả
        $response = curl_exec($curl);
        // Kiểm tra và xử lý kết quả
        if ($response === false) {
            $error = curl_error($curl);
//            echo "Có lỗi xảy ra khi gửi yêu cầu: {$error}";
        } else {
            $responseData = json_decode($response, true);
            if ($responseData['ok'] === true) {
//                echo 'Tin nhắn đã được gửi thành công qua Telegram!';
            } else {
//                echo 'Có lỗi xảy ra khi gửi tin nhắn qua Telegram.';
            }
        }

        // Đóng cURL request
        curl_close($curl);
    }
    public static function SupersendTelegramMessage( $data )
    {

        //$telegramApiKey = '5175382144:AAEFgtXE4DDkGigkt7-x8yV9ahn3EAmgb-4';
        $telegramApiKey='5804977775:AAEZ-ag6Be9-8Qb3QUmpuoeceEQtlsEz3tM';
        $chatId = '-4015306721'; 
        $message = json_encode($data);;

        // Tạo payload cho yêu cầu
        $payload = [
            'chat_id' => $chatId,
            'text' => $message,
        ];
        // Chuyển đổi payload thành định dạng JSON
        $jsonData = json_encode($payload);
        // Tạo cURL request
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, "https://api.telegram.org/bot{$telegramApiKey}/sendMessage");
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $jsonData);
        curl_setopt($curl, CURLOPT_HTTPHEADER, ['Content-Type: application/json']); 
        // Gửi yêu cầu và lấy kết quả
        $response = curl_exec($curl);
        // Kiểm tra và xử lý kết quả
        if ($response === false) {
            $error = curl_error($curl);
//            echo "Có lỗi xảy ra khi gửi yêu cầu: {$error}";
        } else {
            $responseData = json_decode($response, true);
            if ($responseData['ok'] === true) {
            } else {
               
            }
        }

        // Đóng cURL request
        curl_close($curl);
    }
}
