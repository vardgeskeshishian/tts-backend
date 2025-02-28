<?php


namespace App\Constants;

interface FinancesEnv
{
    const BALANCE_DATE_FORMAT = 'Y-m';

    // user types
    const USER_TYPE_AUTHOR = 'author';
    const USER_TYPE_PARTNER = 'partner';

    // balance statuses
    const BALANCE_STATUS_AWAITING = 'awaiting';
    const BALANCE_STATUS_CONFIRMED = 'confirmed';

    // detailed balance source types
    const SOURCE_TYPE_ORDER_ITEM = 'order_item';
    const SOURCE_TYPE_A_DOWNLOAD = 'a_download';
    const SOURCE_TYPE_P_SUBSCRIPTION = 'p_subscription';
}
