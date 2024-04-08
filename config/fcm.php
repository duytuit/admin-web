<?php

return [
    'driver' => env('FCM_PROTOCOL', 'http'),
    'log_enabled' => false,
    'http'=>[
        'server_key' => env('FCM_BANQUANLY_SERVER_KEY', 'Your FCM server key'),
        'sender_id' => env('FCM_BANQUANLY_SENDER_ID', 'Your sender id'),
        'server_send_url' => 'https://fcm.googleapis.com/fcm/send',
        'server_group_url' => 'https://android.googleapis.com/gcm/notification',
        'timeout' => 30.0, // in second
    ],


    'cudan' => [
        'server_key' => env('CUDAN_FCM_SERVER_KEY', 'Your FCM server key'),
        'sender_id' => env('CUDAN_FCM_SENDER_ID', 'Your sender id'),
        'server_send_url' => 'https://fcm.googleapis.com/fcm/send',
        'server_group_url' => 'https://android.googleapis.com/gcm/notification',
        'timeout' => 30.0, // in second
    ],
    'banquanly'=>[
        'server_key' => env('FCM_BANQUANLY_SERVER_KEY', 'Your FCM server key'),
        'sender_id' => env('FCM_BANQUANLY_SENDER_ID', 'Your sender id'),
        'server_send_url' => 'https://fcm.googleapis.com/fcm/send',
        'server_group_url' => 'https://android.googleapis.com/gcm/notification',
        'timeout' => 30.0, // in second
    ],
    'asahi' => [
        'server_key' => env('ASAHI_FCM_SERVER_KEY', 'Your FCM server key'),
        'sender_id' => env('ASAHI_FCM_SENDER_ID', 'Your sender id'),
        'server_send_url' => 'https://fcm.googleapis.com/fcm/send',
        'server_group_url' => 'https://android.googleapis.com/gcm/notification',
        'timeout' => 30.0, // in second
    ],
];
