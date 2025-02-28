<?php

namespace App\Vendor\MailerLiteForked\Api;

use App\Vendor\MailerLiteForked\Common\ApiAbstract;
use Exception;

/**
 * Class Groups
 *
 * @package App\Vendor\MailerLiteForked\Api
 */
class Groups extends ApiAbstract
{
    protected $endpoint = 'groups';

    /**
     * Get subscribers from group
     * @param int $groupId
     * @param null $type
     * @param array $params
     * @return mixed [type]
     * @throws Exception
     */
    public function getSubscribers($groupId, $type = null, $params = [])
    {
        $endpoint = $this->endpoint . '/' . $groupId . '/subscribers';

        if ($type !== null) {
            $endpoint .=  '/' . $type;
        }

        $params = array_merge($this->prepareParams(), $params);

        $response = $this->restClient->get($endpoint, $params);

        return $response['body'];
    }

    /**
     * Get single subscriber from group
     *
     * @param $groupId
     * @param $subscriberId
     * @return mixed
     * @throws Exception
     */
    public function getSubscriber($groupId, $subscriberId)
    {
        $endpoint = $this->endpoint . '/' . $groupId . '/subscribers/' . urlencode($subscriberId);

        $response = $this->restClient->get($endpoint);

        return $response['body'];
    }


    /**
     * Add single subscriber to group
     *
     * @param int $groupId
     * @param array $subscriberData
     * @param array $params
     *
     * @return mixed [type]
     * @throws Exception
     */
    public function addSubscriber($groupId, $subscriberData = [], $params = [])
    {
        $endpoint = $this->endpoint . '/' . $groupId . '/subscribers';

        $response = $this->restClient->post($endpoint, $subscriberData);

        return $response['body'];
    }

    /**
     * Batch add subscribers to group
     *
     * @param int $groupId
     * @param array $subscribers
     * @param array $options
     * @return mixed [type]
     * @throws Exception
     */
    public function importSubscribers(
        $groupId,
        $subscribers,
        $options = [
            'resubscribe' => false,
            'autoresponders' => false
        ]
    ) {
        $endpoint = $this->endpoint . '/' . $groupId . '/subscribers/import';

        $response = $this->restClient->post($endpoint, array_merge(['subscribers' => $subscribers], $options));

        return $response['body'];
    }
}
