<?php

namespace App\Services;

use App\Models\PaddleApiKey;
use Exception;
use GuzzleHttp\Promise\PromiseInterface;
use Illuminate\Http\Client\RequestException;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;

class PaddleApiService
{
    public string $host;

    private const HTTP_RETRIES = 3;

    public function __construct()
    {
        $this->host = app('env') == 'production' ?
            'https://api.paddle.com/' : 'https://sandbox-api.paddle.com/';
    }

    /**
     * @param string $url
     * @param array $get_params
     * @param string|null $token_authorization
     * @return PromiseInterface|Response
     * @throws RequestException
     */
    public function get(string $url, array $get_params = [], ?string $token_authorization = null): PromiseInterface|Response
    {
        $result = Http::retry(self::HTTP_RETRIES);

        if (!is_null($token_authorization)) {
            $result = $result->withHeaders([
                'Authorization' => 'Bearer '.$token_authorization,
            ]);
        }

        return $result
            ->get($url, $get_params)
            ->throw();
    }

    /**
     * @param string $url
     * @param array $body
     * @param string|null $token_authorization
     * @return PromiseInterface|Response
     * @throws RequestException
     */
    public function post(string $url, array $body = [], ?string $token_authorization = null): PromiseInterface|Response
    {
        $result = Http::retry(self::HTTP_RETRIES);

        if (!is_null($token_authorization)) {
            $result = $result->withHeaders([
                'Authorization' => 'Bearer '.$token_authorization,
            ]);
        }

        return $result
            ->asForm()
            ->post($url, $body)
            ->throw();
    }

    /**
     * @param string $url
     * @param array $body
     * @param string|null $token_authorization
     * @return PromiseInterface|Response
     * @throws RequestException
     */
    public function patch(string $url, array $body = [], ?string $token_authorization = null): PromiseInterface|Response
    {
        $result = Http::retry(self::HTTP_RETRIES);

        if (!is_null($token_authorization)) {
            $result = $result->withHeaders([
                'Authorization' => 'Bearer '.$token_authorization,
            ]);
        }

        return $result
            ->patch($url, $body)
            ->throw();
    }

    /**
     * @param mixed $token
     * @param string $url
     * @param array $get_params
     * @return array
     * @throws RequestException
     */
    private function outputResult(mixed $token, string $url, array $get_params): array
    {
        if (is_null($token)) {
            $response = __('Token not found');
            throw new Exception($response);
        }

        try {
            $result = $this->get($url, $get_params, $token);
        } catch (RequestException $e) {
            throw new RequestException($e->response);
        }

        return [
            'result' => true,
            'data' => json_decode($result->body(), true)['data']
        ];
    }


    /**
     * @param string|null $after
     * @param string|array|null $id
     * @param int|null $per_page
     * @param string|array|null $status
     * @return array
     * @throws RequestException
     */
    public function getPrices(
        ?string           $after = null,
        string|array|null $id = null,
        ?int $per_page = null,
        string|array|null $status = null
    ): array
    {
        $token = PaddleApiKey::first()?->key;

        $get_params = [];
        if(!is_null($after))
            $get_params['after'] = $after;

        if(!is_null($status))
            $get_params['status'] = $status;

        if(!is_null($id))
            $get_params['id'] = $id;

        if(!is_null($per_page))
            $get_params['per_page'] = $per_page;

        $url = $this->host.'prices';

        return $this->outputResult($token, $url, $get_params);
    }

    /**
     * @param string|null $after
     * @param string|array|null $id
     * @param int|null $per_page
     * @param string|array|null $status
     * @return array
     * @throws RequestException
     */
    public function getProducts(
        ?string           $after = null,
        string|array|null $id = null,
        ?int $per_page = null,
        string|array|null $status = null
    ): array
    {
        $token = PaddleApiKey::first()?->key;

        $get_params = [];
        if(!is_null($after))
            $get_params['after'] = $after;

        if(!is_null($status))
            $get_params['status'] = $status;

        if(!is_null($id))
            $get_params['id'] = $id;

        if(!is_null($per_page))
            $get_params['per_page'] = $per_page;

        $url = $this->host.'products';

        return $this->outputResult($token, $url, $get_params);
    }

    /**
     * @param string $transaction_id
     * @return array
     * @throws RequestException
     */
    public function getTransaction(string $transaction_id): array
    {
        $token = PaddleApiKey::first()?->key;

        $url = $this->host.'transactions/'.$transaction_id;

        return $this->outputResult($token, $url, []);
    }

    /**
     * @param string $subscription_id
     * @return array
     * @throws RequestException
     */
    public function getSubscription(string $subscription_id): array
    {
        $token = PaddleApiKey::first()?->key;

        $url = $this->host.'subscriptions/'.$subscription_id;

        return $this->outputResult($token, $url, []);
    }
}