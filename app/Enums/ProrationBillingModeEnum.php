<?php

namespace App\Enums;

enum ProrationBillingModeEnum: string
{
    case PRORATED_IMMEDIATELY = 'prorated_immediately';

    case FULL_IMMEDIATELY = 'full_immediately';

    case PRORATED_NEXT_BILLING_PERIOD = 'prorated_next_billing_period';

    case FULL_NEXT_BILLING_PERIOD = 'full_next_billing_period';

    case DO_NOT_BILL = 'do_not_bill';
}