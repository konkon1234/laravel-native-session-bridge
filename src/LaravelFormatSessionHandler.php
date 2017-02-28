<?php

namespace Konkon1234\LaravelNativeSessionBridge;

use Carbon\Carbon;
use Illuminate\Session\DatabaseSessionHandler;

/**
 * Class LaravelFormatSessionHandler
 * @package Konkon1234\LaravelNativeSessionBridge
 */
class LaravelFormatSessionHandler extends DatabaseSessionHandler
{
    /**
     * Get the default payload for the session.
     *
     * @param  string  $data
     * @return array
     */
    protected function getDefaultPayload($data)
    {
        $payload = [
            'payload' => base64_encode($data),
            'last_activity' => Carbon::now()->getTimestamp(),
            'ip_address' => isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '',
            'user_agent' => isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '',
        ];

        return $payload;
    }


}