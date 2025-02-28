<?php

namespace App\Enums;

enum TypeWebhookEnum: string
{
    case CLASSIC = 'classic';

    case BILLING = 'billing';
}