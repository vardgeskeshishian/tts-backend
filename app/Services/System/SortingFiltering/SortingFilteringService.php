<?php

namespace App\Services\System\SortingFiltering;

use Exception;

class SortingFilteringService
{
    private $handlers = [
        'partnership-detailed-tracks' => '',
        'partnership-detailed-earnings' => '',
    ];

    /**
     * @var null
     */
    private $instance = null;


    /**
     *
     *
     * result: get sorting and filtering options for given model
     */

    /**
     * @param $instance
     * @return $this
     */
    public function setInstance($instance)
    {
        $this->instance = $instance;



        return $this;
    }

    /**
     * @throws Exception
     */
    protected function getSortingOptions()
    {
        if (!$this->instance) {
            throw new Exception("[SortingFiltering]: model is not set");
        }
    }
}
