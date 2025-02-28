<?php


namespace App\Constants;

interface SubmissionsEnv
{
    const COOP_TYPE_EXC = 'exclusive';
    const COOP_TYPE_NOEXC = 'non-exclusive';

    /**
     * New
     * - Approved
     * - Soft reject (нужны правки)
     * - Resubmitted (перезагружен после внесенных правок)
     * - Hard reject (трек окончательно не принят)
     * - Delivery complete (загрузка версий завершена)
     * - Published (опубликован на сайте)
     */

    const STATUS_NEW = 'new';
    const STATUS_APPROVED = 'approved';
    const STATUS_SOFT_R = 'soft_reject';
    const STATUS_HARD_R = 'hard_reject';
    const STATUS_RESUB = 'resubmitted';
    const STATUS_DELIVERY_C = 'delivery_completed';
    const STATUS_PUBLISHED = 'published';

    const STATUSES = [
        self::STATUS_NEW,
        self::STATUS_APPROVED,
        self::STATUS_SOFT_R,
        self::STATUS_HARD_R,
        self::STATUS_RESUB,
        self::STATUS_DELIVERY_C,
        self::STATUS_PUBLISHED,
    ];
    
    const REVIEWER_STATUSES = [
        self::STATUS_NEW,
        self::STATUS_APPROVED,
        self::STATUS_SOFT_R,
        self::STATUS_HARD_R,
    ];

    const FINAL_STATUSES = [
        self::STATUS_NEW,
        self::STATUS_APPROVED,
        self::STATUS_SOFT_R,
        self::STATUS_HARD_R,
        self::STATUS_PUBLISHED
    ];

    const COMMENT_TYPE_PRIVATE = 'private';
    const COMMENT_TYPE_PUBLIC = 'public';
}
