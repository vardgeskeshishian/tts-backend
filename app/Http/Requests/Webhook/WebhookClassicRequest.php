<?php

namespace App\Http\Requests\Webhook;

use Illuminate\Foundation\Http\FormRequest;
use Log;

class WebhookClassicRequest extends FormRequest
{
    public function verifySign(): false|int
    {
        $public_key_string = "-----BEGIN PUBLIC KEY-----"."\n".
            "MIICIjANBgkqhkiG9w0BAQEFAAOCAg8AMIICCgKCAgEAquRRQYwXNzvKc0hkuHdN"."\n".
            "caikwh+O+h0oUSnbmEGF5dJFsopkzTXvKmB3MZ1iRQQ5DOTK8d6v/5+MkJD6UBJO"."\n".
            "12PASgcKVelY3izFNK8bhFsnKGjR+EW7fKPCB7snBWyo2eSrSvejJ7te9ElScqNq"."\n".
            "KYzQm/tCLv7meIyOhtRLxq2YfMFKojhuxlm7b1/lf2Hd5kHFZtPkl9XJFxHbJ5u2"."\n".
            "LemnlThZSgzXHlftO9Uk+f3xDQmzLAKVDfTbsjPWIYUViVjfJJFwkR1w1M/76L1i"."\n".
            "S9pTXsnJ0zgppuUAsQAf4tnCy1KZ4cIAG2Imr2ejK/XIYcMafvYNZTlpp7Zj+Ri4"."\n".
            "Zpxr9QWyz680dh0Wf+ns0apswtxwffGSlJafTW/SzQ1uBbrFO2V8jNjw/nXll4b5"."\n".
            "dBhExvQTn8XOdnwNAt6RRW5/pg9pRcKSGcfLpQiVDZZtJC5O8ip9ThDHYdNgc5e1"."\n".
            "/oEBt0r4hBJk1jeTouRz9SV8HWkf1OePZi8u5H6AZUCX4riyhYwS5Z6OtD31GY8T"."\n".
            "/8a6zrbLIRqTuXwQ0xlEgtyebJ6fmjP77oRm7NufsunOE0tk93Me8Znq2erIWe4E"."\n".
            "sYli+lYDNjRcGLapS512GfssmyQDt1S6RJ/9BxqDT7L9LTY9yT+RbSao/JFP+tRn"."\n".
            "xM2lFxK87gHVcvYl7JYa+s8CAwEAAQ=="."\n".
            "-----END PUBLIC KEY-----";
        $public_key = openssl_get_publickey($public_key_string);

        $fields = $this->toArray();
        $signature = base64_decode($fields['p_signature']);

        unset($fields['p_signature']);

        ksort($fields);
        foreach($fields as $k => $v) {
            if(!in_array(gettype($v), array('object', 'array'))) {
                $fields[$k] = "$v";
            }
        }
        $data = serialize($fields);
        return openssl_verify($data, $signature, $public_key);
    }
}