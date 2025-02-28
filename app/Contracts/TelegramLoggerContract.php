<?php


namespace App\Contracts;

use Monolog\Logger;

interface TelegramLoggerContract
{
    const CHANNEL_DEBUG_ID = -450046301;
    const CHANNEL_USERS_ID = -492693203;
    const CHANNEL_CRITICAL_ID = -476555890;
    const CHANNEL_ANALYTICS_ID = -213344406;
    const CHANNEL_REQUESTS_ID = -295842250;

    public function pushToChat(int $chatId, $title, $context = [], $level = Logger::DEBUG, $toDebug = []);
}
