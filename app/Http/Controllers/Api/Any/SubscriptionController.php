<?php

namespace App\Http\Controllers\Api\Any;

use App\Factories\SubscriptionFactory;

class SubscriptionController
{
    public function alert(SubscriptionFactory $factory)
    {
        $factory->work();
    }
}
