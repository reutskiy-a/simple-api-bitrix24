<?php

declare(strict_types=1);

namespace SimpleApiBitrix24\Connectors\Handlers;

use SimpleApiBitrix24\Connectors\Handlers\Dto\ErrorContext;
use SimpleApiBitrix24\Connectors\Interfaces\ErrorHandlerInterface;
use SimpleApiBitrix24\Exceptions\EmptyResponseException;

class EmptyResponseHandler implements ErrorHandlerInterface
{
    public const USLEEP_DEFAULT = 500000;
    private const ATTEMPTS_LIMIT = 6;
    private int $requestRetryAttempts = 0;
    private int $usleep;
    private bool $isHandlingEnabled;
    private const EXCEPTION_MESSAGE = 'Bitrix24 returned an empty response to your request.';

    public function __construct(bool $isHandlingEnabled = true, int $usleep = self::USLEEP_DEFAULT)
    {
        $this->isHandlingEnabled = $isHandlingEnabled;
        $this->usleep = $usleep;
    }

    public function canHandle(ErrorContext $errorContext): bool
    {
        return empty($errorContext->response);
    }

    public function handle(ErrorContext $errorContext): bool
    {
        $this->requestRetryAttempts++;

        if (! $this->isHandlingEnabled || $this->requestRetryAttempts >= self::ATTEMPTS_LIMIT) {
            throw new EmptyResponseException(self::EXCEPTION_MESSAGE);
        }

        if ($this->requestRetryAttempts < self::ATTEMPTS_LIMIT) {
            usleep($this->usleep);
            return true;
        }

        return false;
    }
}
