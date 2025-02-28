<?php


namespace App\Services\MailerLite;

use App\Contracts\MailerLiteBatcherContract;
use App\Jobs\MailerLiteEvent;
use App\Models\MailerLiteEventQueue;
use App\Vendor\MailerLiteForked\Common\BatchRequest;

class MailerLiteBatcher implements MailerLiteBatcherContract
{
    protected $batch = [];

    protected static $instance;

    public static function getInstance()
    {
        if (!isset(self::$instance)) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * @param int|null $userId
     * @param string $methodName
     * @param string $path
     * @param string $options
     * @param array $data
     */
    public function pushToBatch(?int $userId, string $methodName, string $path, string $options, $data = [])
    {
        if (MailerLiteEventQueue::where([
            'user_id' => $userId,
            'path' => $path,
            'method' => $methodName,
            'options' => $options,
            'was_sent' => false,
        ])->exists()) {
            return;
        }

        MailerLiteEventQueue::create([
            'user_id' => $userId,
            'method' => $methodName,
            'path' => $path,
            'options' => $options,
            'data' => $data,
        ]);
    }

    public function sendBatch()
    {
        $data = MailerLiteEventQueue::where('was_sent', false)->limit(20)->get();

        $requests = [];

        foreach ($data as $item) {
            $requests[] = (new BatchRequest())
                ->setMethod($item->method)
                ->setPath($item->path)
                ->setBody($item->data);
        }

        MailerLiteEvent::dispatch($data->pluck('id')->toArray(), $requests);
    }
}
