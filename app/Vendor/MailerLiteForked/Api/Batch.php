<?php

namespace App\Vendor\MailerLiteForked\Api;

use App\Vendor\MailerLiteForked\Common\ApiAbstract;
use App\Vendor\MailerLiteForked\Common\BatchRequest;
use App\Vendor\MailerLiteForked\Common\Collection;
use App\Vendor\MailerLiteForked\Exceptions\MailerLiteSdkException;

/**
 * Class Batch
 *
 * @package App\Vendor\MailerLiteForked\Api
 */
class Batch extends ApiAbstract
{

    protected $endpoint = 'batch';

    /**
     * @param BatchRequest[] $requests
     *
     * @return Collection
     * @throws MailerLiteSdkException
     */
    public function send(array $requests)
    {
	    if (!count($requests)) {
		    return [];
            //throw new MailerLiteSdkException("Provide at least one request");
        }

        $arrayOfRequests = [];

        foreach ($requests as $request) {
            if (!is_object($request) || get_class($request) !== BatchRequest::class) {
                throw new MailerLiteSdkException("All requests must be of type " . BatchRequest::class);
            }

            $arrayOfRequests[] = $request->toArray();
        }


        $response = $this->restClient->post($this->endpoint, [
            'requests' => $arrayOfRequests,
        ]);

        return $response['body'];
    }

}
