<?php

namespace App\Enums;

enum StatusSubscriptionEnum:string
{
    case ACTIVE = 'active';

    case CANCELED = 'canceled';

    case PAST_DUE = 'past_due';

    case PAUSED = 'paused';

    case TRIALING = 'trialing';
}
