<?php


namespace App\Services;

use Monolog\Logger;
use Telegram\Bot\Api;
use App\Jobs\TelegramSendMessageJob;
use App\Contracts\TelegramLoggerContract;
use Telegram\Bot\Exceptions\TelegramSDKException;

class TelegramLoggerService implements TelegramLoggerContract
{
    /**
     * @var Api|null
     */
    private ?Api $bot = null;

    /**
     * TelegramLogger constructor.
     *
     * @throws TelegramSDKException
     */
    public function __construct()
    {
        if (config('app.env') !== 'dev' && config('logging.telegram-logger-token')) {
            $this->bot = new Api(config('logging.telegram-logger-token'), false);
        }
    }

    /**
     * @param int $chatId
     * @param $title
     * @param array $context
     * @param int $level
     * @param array $toDebug
     */
    public function pushToChat(int $chatId, $title, $context = [], $level = Logger::DEBUG, $toDebug = [])
    {
        $message = '+ *Message*: ' . $title . PHP_EOL;
        $message .= '+ *User ID*: ' . (auth()->user() ? auth()->user()->id : 'No User') . PHP_EOL;
        $message .= '--------------------------------' . PHP_EOL;

        foreach ($context as $key => $item) {
            $data = is_array($item) ? json_encode($item, JSON_PRETTY_PRINT) : $item;
            $message .= "*{$key}*: " . '```json' . PHP_EOL . $data . PHP_EOL . '```';
        }

        $messageArray = [
            'chat_id' => $chatId,
            'text' => $message,
            'parse_mode' => 'Markdown',
            'disable_web_page_preview' => true,
            'disable_notifications' => true,
        ];

        logs('debug')->info($chatId, array_merge($messageArray, ['toDebug' => $toDebug]));

        if (!$this->bot) {
            return;
        }

        dispatch_now(new TelegramSendMessageJob($this->bot, $messageArray));
    }
}
