<?php

namespace App\Vendor\MailerLiteForked;

use App\Vendor\MailerLiteForked\Api\Batch;
use App\Vendor\MailerLiteForked\Api\Groups;
use App\Vendor\MailerLiteForked\Api\Subscribers;

use App\Vendor\MailerLiteForked\Common\ApiConstants;
use App\Vendor\MailerLiteForked\Common\RestClient;

/**
 * Class MailerLite
 *
 * @package App\Vendor\MailerLiteForked
 */
class MailerLite
{

    /**
     * @var null | string
     */
    protected $apiKey;

    /**
     * @var RestClient
     */
    public $restClient;

    public function __construct()
    {
        $this->apiKey = config('mailer_lite.code');

        $this->restClient = new RestClient($this->getBaseUrl(), $this->apiKey);
    }

    /**
     * @return Groups
     */
    public function groups()
    {
        return new Groups($this->restClient);
    }

    /**
     * @return Subscribers
     */
    public function subscribers()
    {
        return new Subscribers($this->restClient);
    }

    /**
     * @return Batch
     */
    public function batch()
    {
        return new Batch($this->restClient);
    }

    /**
     * @param string $version
     * @return string
     */
    public function getBaseUrl($version = ApiConstants::VERSION)
    {
        return ApiConstants::BASE_URL . $version . '/';
    }

}
