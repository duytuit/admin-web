<?php

namespace App\Services\FCM;

use LaravelFCM\Request\Request;
use LaravelFCM\Message\Topics;
use LaravelFCM\Message\Options;
use LaravelFCM\Message\PayloadData;
use LaravelFCM\Message\PayloadNotification;

/**
 * /Users/letao/Code/php/datxanh/push/app/Services/FCM/DXCustomRequest.php
 */
class DXCustomRequest extends Request
{

    protected $config;

    public function __construct($to, Options $options = null, PayloadNotification $notification = null, PayloadData $data = null, Topics $topic = null)
    {
        parent::__construct($to, $options, $notification, $data, $topic);
    }

    public function setConfigName($config = 'fcm.http')
    {
        // se viet phan code de lay ra config FCM trong database và gan vao biết $this->config
        $this->config = app('config')->get($config, []);

        return $this;
    }
}