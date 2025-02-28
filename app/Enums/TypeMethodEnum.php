<?php

namespace App\Enums;

enum TypeMethodEnum: string
{
    case GET = 'get';

    case POST = 'post';

    case PATCH = 'patch';
}