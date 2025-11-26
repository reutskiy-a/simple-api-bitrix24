<?php

declare(strict_types=1);

namespace SimpleApiBitrix24\Constants;

/**
 * Defines constants for database column names related to Bitrix24 API tokens.
 * These constants are provided for convenience and consistency but are not mandatory to use.
 * Feel free to use your own column names if needed.
 */
class DatabaseConstants
{
    const TABLE_NAME = 'b24_users';
    const USER_ID_COLUMN_NAME = 'user_id';
    const MEMBER_ID_COLUMN_NAME = 'member_id';
    const IS_ADMIN_COLUMN_NAME = 'is_admin';
    const AUTH_TOKEN_COLUMN_NAME = 'auth_token';
    const REFRESH_TOKEN_COLUMN_NAME = 'refresh_token';
    const DOMAIN_COLUMN_NAME = 'domain';
    const CLIENT_ID_COLUMN_NAME = 'client_id';
    const CLIENT_SECRET_COLUMN_NAME = 'client_secret';
    const CREATED_AT_COLUMN_NAME = 'created_at';
    const UPDATED_AT_COLUMN_NAME = 'updated_at';
}
