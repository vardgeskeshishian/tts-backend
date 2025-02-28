<?php

namespace App\Enums;

use App\Models\UserBillingSubscription;

enum LimitKeysWhiteListEnum: string
{
    case CREATOR = 'creator_whitelists';
    case BUSSINESS = 'bussiness_whitelists';
    case FREE = 'free_whitelists';

    public static function getKeyByProductId(string $product_id): LimitKeysWhiteListEnum
    {
        return match ($product_id)
        {
            UserBillingSubscription::CREATOR_PRODUCT_ID => self::CREATOR,
            UserBillingSubscription::BUSSINESS_PRODUCT_ID => self::BUSSINESS,
            UserBillingSubscription::FREE_PRODUCT_ID => self::FREE,
        };
    }
}