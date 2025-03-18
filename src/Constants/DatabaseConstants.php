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
    const TABLE_NAME = 'api_tokens_bitrix24';
    const PRIMARY_KEY_COLUMN_NAME = 'id';
    const MEMBER_ID_COLUMN_NAME = 'member_id';
    const ACCESS_TOKEN_COLUMN_NAME = 'access_token';
    const EXPIRES_IN_COLUMN_NAME = 'expires_in';
    const APPLICATION_TOKEN_COLUMN_NAME = 'application_token';
    const REFRESH_TOKEN_COLUMN_NAME = 'refresh_token';
    const DOMAIN_COLUMN_NAME = 'domain';
    const CLIENT_END_POINT_COLUMN_NAME = 'client_endpoint';
    const CLIENT_ID_COLUMN_NAME = 'client_id';
    const CLIENT_SECRET_COLUMN_NAME = 'client_secret';
}
