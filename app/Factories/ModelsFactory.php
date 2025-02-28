<?php

namespace App\Factories;

class ModelsFactory
{
    public function getModelFromName($name)
    {

        $model = match ($name) {
            'current-user' => auth()->user(),
            default => resolve($name),
        };

        return $model;
    }
}
