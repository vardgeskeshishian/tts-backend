<?php

namespace App\Vendor\MailerLiteForked\Common;

use Exception;
use App\Vendor\MailerLiteForked\MailerLite;

abstract class ApiAbstract
{
    protected $restClient;

    protected $endpoint;

    private $limit = null;

    private $offset = null;

    private $where = null;

    public function __construct(RestClient $restClient)
    {
        $this->restClient = $restClient;
    }

    /**
     * Get collection of items
     *
     * @param array $fields
     *
     * @return Collection [type]
     * @throws Exception
     */
    public function get($fields = ['*'])
    {
        $params = $this->prepareParams();

        if (!empty($fields) && is_array($fields) && $fields != ['*']) {
            $params['fields'] = $fields;
        }

        $response = $this->restClient->get($this->endpoint, $params);

        return $this->generateCollection($response['body']);
    }

    /**
     * Get single item
     *
     * @param int|string $id Id can be Subscribers ID or his email address
     *
     * @return mixed [type]
     * @throws Exception
     */
    public function find($id)
    {
        if (empty($id)) {
            throw new Exception('ID must be set');
        }

        $response = $this->restClient->get($this->endpoint . '/' . $id);

        return $response['body'];
    }

    /**
     * Create new item
     *
     * @param array $data
     *
     * @return mixed [type]
     * @throws Exception
     */
    public function create($data)
    {
        return $this->restClient->post($this->endpoint, $data)['body'];
    }

    /**
     * Update an item
     *
     * @param int $id
     * @param array $data
     *
     * @return mixed [type]
     */
    public function update($id, $data): array
    {
        return [
            'method' => BatchRequest::METHOD_PUT,
            'path' => (new MailerLite)->getBaseUrl() . $this->endpoint . '/' . $id,
            'data' => $data,
        ];
    }

    /**
     * Return only count of items
     *
     * @return mixed [type]
     * @throws Exception
     */
    public function count()
    {
        $response = $this->restClient->get($this->endpoint . '/count');

        return $response['body'];
    }

    /**
     * Set size of limit in query
     *
     * @param  [type] $limit
     *
     * @return ApiAbstract [type]
     */
    public function limit($limit)
    {
        $this->limit = $limit;

        return $this;
    }

    /**
     * Set size of offset in query
     *
     * @param [type] $offset
     *
     * @return ApiAbstract
     */
    public function offset($offset)
    {
        $this->offset = $offset;

        return $this;
    }

    /**
     * Set where conditions
     *
     * @param  [type] $column
     *
     * @return ApiAbstract [type]
     */
    public function where($column)
    {
        if (is_array($column)) {
            $this->where = $column;
        }

        return $this;
    }

    /**
     * Return collection of objects
     *
     * @param  [type] $items
     *
     * @return Collection [type]
     */
    public function generateCollection($items)
    {
        if (!is_array($items)) {
            $items = [$items];
        }

        return new Collection($items);
    }

    /**
     * Prepare query parameters
     *
     * @return array [type]
     */
    protected function prepareParams()
    {
        $params = [];

        if (!empty($this->where) && is_array($this->where)) {
            $params['filters'] = $this->where;
        }

        if (!empty($this->offset)) {
            $params['offset'] = $this->offset;
        }

        if (!empty($this->limit)) {
            $params['limit'] = $this->limit;
        }

        if (!empty($this->_orders) && is_array($this->_orders)) {
            foreach ($this->_orders as $field => $order) {
                $params['order_by'][$field] = $order;
            }
        }

        return $params;
    }

}
