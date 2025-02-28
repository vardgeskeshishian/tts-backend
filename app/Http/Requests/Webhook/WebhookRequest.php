<?php

namespace App\Http\Requests\Webhook;

use Illuminate\Foundation\Http\FormRequest;
use Paddle\SDK\Notifications\PaddleSignature;

class WebhookRequest extends FormRequest
{
    /**
     * @return bool
     */
    public function verifySign(): bool
    {
        $signatureData = $this->header(PaddleSignature::HEADER);
        $signature = PaddleSignature::parse($signatureData);
        $hash = hash_hmac('sha256', $signature->timestamp.':'.json_encode($this->all()),
            env('BILLING_WEBHOOK_SECRET_KEY'));
        return hash_equals($hash, $signature->hashes['h1'][0]);
    }
}