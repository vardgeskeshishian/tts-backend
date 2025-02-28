<?php


namespace App\Constants;

interface UserEnv
{
    const ROLE_ADMIN = 'admin';

    const ROLE_USER = 'user';
    const ROLE_PARTNER = 'partner';
    const ROLE_AUTHOR = 'author';

    const AVAILABLE_GENERAL_ROLES = [self::ROLE_USER, self::ROLE_PARTNER, self::ROLE_AUTHOR];
}
