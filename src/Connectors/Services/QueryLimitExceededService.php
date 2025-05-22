<?php

declare(strict_types=1);

namespace SimpleApiBitrix24\Connectors\Services;

use SimpleApiBitrix24\Exceptions\QueryLimitExceededException;

class QueryLimitExceededService
{
    public const USLEEP_DEFAULT = 1000000;
    private int $usleep;
    private bool $handleEnabled;
    private const ERROR_RESPONSE = [
        'error' => 'QUERY_LIMIT_EXCEEDED',
        'error_description' => 'Too many requests'
    ];

    public function __construct(bool $handleEnabled = false, int $usleep = self::USLEEP_DEFAULT)
    {
        $this->handleEnabled = $handleEnabled;
        $this->usleep = $usleep;
    }


    public function shouldTheRequestBeRepeated(array $response): bool
    {
        if ($this->isErrorRelatedToService($response)) {

            if (! $this->isAttemptAllowed()) {
                throw new QueryLimitExceededException(json_encode($response));
            }

            usleep($this->usleep);
            return true;
        }

        return false;
    }

    private function isErrorRelatedToService($response): bool
    {
        if (isset($response['error']) && $response['error'] === self::ERROR_RESPONSE['error']) {
            return true;
        }

        return false;
    }

    private function isAttemptAllowed(): bool
    {
        if (false === $this->handleEnabled) {
            return false;
        }

        return true;
    }
}
