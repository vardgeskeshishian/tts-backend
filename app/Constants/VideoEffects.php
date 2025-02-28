<?php


namespace App\Constants;

class VideoEffects
{
    const STATUS_NEW = 1;
    const STATUS_SOFT_REJECT = 2;
    const STATUS_RESUBMITTED = 3;
    const STATUS_APPROVED = 4;
    const STATUS_PUBLISHED = 5;
    const STATUS_HARD_REJECT = 6;
    const STATUS_EMPTY = 100;

    const STATUSES = [
        self::STATUS_NEW => "NEW",
        self::STATUS_SOFT_REJECT => "SOFT_REJECT",
        self::STATUS_RESUBMITTED => "RESUBMITTED",
        self::STATUS_APPROVED => "APPROVED",
        self::STATUS_PUBLISHED => "PUBLISHED",
        self::STATUS_HARD_REJECT => "HARD_REJECT",
        self::STATUS_EMPTY => "EMPTY",
    ];

    const ADMIN_STATUSES = [
        self::STATUS_SOFT_REJECT,
        self::STATUS_APPROVED,
        self::STATUS_PUBLISHED,
        self::STATUS_HARD_REJECT
    ];
}
