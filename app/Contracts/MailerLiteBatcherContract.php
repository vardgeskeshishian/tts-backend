<?php


namespace App\Contracts;

interface MailerLiteBatcherContract
{
    public function pushToBatch(?int $userId, string $methodName, string $path, string $options, $data = []);
    public function sendBatch();
}
