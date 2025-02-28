<?php

namespace App\Enums;

enum EffectiveFromEnum: string
{
    case NEXT_BILLING_PERIOD = 'next_billing_period';

    case IMMEDIATELY = 'immediately';
}