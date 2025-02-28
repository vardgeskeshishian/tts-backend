<?php

namespace App\Enums;

use App\Models\UserBillingSubscription;

enum LimitKeysClaimsEnum: string
{
    case CREATOR = 'creator_claims';
    case BUSSINESS = 'bussiness_claims';
    case FREE = 'free_claims';

    /**
     * @param string $product_id
     * @return LimitKeysClaimsEnum
     */
    public static function getKeyByProductId(string $product_id): LimitKeysClaimsEnum
    {
        return match ($product_id)
        {
            UserBillingSubscription::CREATOR_PRODUCT_ID => self::CREATOR,
            UserBillingSubscription::BUSSINESS_PRODUCT_ID => self::BUSSINESS,
            UserBillingSubscription::FREE_PRODUCT_ID => self::FREE,
        };
    }
}