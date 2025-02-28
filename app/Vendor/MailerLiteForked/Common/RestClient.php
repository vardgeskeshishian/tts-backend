<?php

namespace App\Vendor\MailerLiteForked\Common;

use App\Contracts\TelegramLoggerContract;
use Curl;
use Exception;
use TelegramLoggerFacade;

class RestClient
{
    public $apiKey;

    public $baseUrl;

    /**
     * @param string $baseUrl
     * @param string $apiKey
     */
    public function __construct(string $baseUrl, string $apiKey)
    {
        $this->baseUrl = $baseUrl;
        $this->apiKey = $apiKey;
    }

    /**
     * Execute GET request
     *
     * @param string $endpointUri
     * @param array $queryString
     * @return array [type]
     * @throws Exception
     */
    public function get($endpointUri, $queryString = [])
    {
        return $this->send('GET', $endpointUri . '?' . http_build_query($queryString));
    }

    /**
     * Execute POST request
     *
     * @param string $endpointUri
     * @param array $data
     * @return array [type]
     * @throws Exception
     */
    public function post($endpointUri, $data = [])
    {
        return $this->send('POST', $endpointUri, $data);
    }

    /**
     * Execute PUT request
     *
     * @param string $endpointUri
     * @param array $putData
     * @return array [type]
     * @throws Exception
     */
    public function put($endpointUri, $putData = [])
    {
        return $this->send('PUT', $endpointUri, $putData);
    }

    /**
     * Execute HTTP request
     *
     * @param string $method
     * @param string $endpointUri
     * @param null $body
     * @param array $headers
     * @return array [type]
     * @throws Exception
     */
    protected function send($method, $endpointUri, $body = null, array $headers = []): array
    {
        $headers = array_merge($headers, self::getDefaultHeaders());
        $endpointUrl = $this->baseUrl . $endpointUri;

        $withHeaders = [];
        foreach ($headers as $name => $value) {
            $withHeaders[] = "$name: $value";
        }

        if (!$body) {
            $body = [];
        }

        $result = [
            'status' => 500,
            'content' => '',
        ];

        try {
            $curl = Curl::to($endpointUrl)
                ->withTimeout(2)
                ->withHeaders($withHeaders)
                ->withData($body)
                ->asJsonRequest()
                ->returnResponseArray();
            $result = $curl->{$method}();
        } catch (Exception  $e) {
            TelegramLoggerFacade::pushToChat(TelegramLoggerContract::CHANNEL_DEBUG_ID, "mailer-lite-headers-dump", [$e->getMessage()]);
        }

        return $this->handleResponse($result);
    }

    /**
     * Handle HTTP response
     *
     * @param array $response
     * @return array [type]
     */
    protected function handleResponse(array $response)
    {
        $result = ['status_code' => $response['status'], 'body' => json_decode($response['content'], true)];

        if (!in_array($result['status_code'], [200, 429, 404])) {
            logs('telegram-debug')->info('mailer-lite-batch-update', [
           'result' => $result,
        ]);
        }
        return $result;
    }

    /**
     * @return array
     */
    protected function getDefaultHeaders()
    {
        $headers = [
            'Content-Type' => 'application/json',
        ];

        if ($this->apiKey) {
            $headers['X-MailerLite-ApiKey'] = $this->apiKey;
        }

        return $headers;
    }
}
