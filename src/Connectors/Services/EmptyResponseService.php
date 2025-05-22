<?php

declare(strict_types=1);

namespace SimpleApiBitrix24\Connectors\Services;

use SimpleApiBitrix24\Exceptions\EmptyResponseException;
use SimpleApiBitrix24\Exceptions\QueryLimitExceededException;

class EmptyResponseService
{
    public const USLEEP_DEFAULT = 500000;
    private int $usleep;
    private bool $handleEnabled;
    private const ERROR_RESPONSE = [];
    private const EXCEPTION_MESSAGE = 'Bitrix24 returned an empty response to your request.';

    public function __construct(bool $handleEnabled = false, int $usleep = self::USLEEP_DEFAULT)
    {
        $this->handleEnabled = $handleEnabled;
        $this->usleep = $usleep;
    }


    public function shouldTheRequestBeRepeated(array $response): bool
    {
        if ($this->isErrorRelatedToService($response)) {

            if (! $this->isAttemptAllowed()) {
                throw new EmptyResponseException(self::EXCEPTION_MESSAGE);
            }

            usleep($this->usleep);
            return true;
        }

        return false;
    }

    private function isErrorRelatedToService($response): bool
    {
        if (is_array($response) && $response === self::ERROR_RESPONSE) {
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
