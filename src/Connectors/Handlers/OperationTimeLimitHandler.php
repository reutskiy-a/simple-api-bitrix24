<?php

declare(strict_types=1);

namespace SimpleApiBitrix24\Connectors\Handlers;

use SimpleApiBitrix24\Connectors\Handlers\Dto\ErrorContext;
use SimpleApiBitrix24\Connectors\Interfaces\ErrorHandlerInterface;
use SimpleApiBitrix24\Exceptions\OperationTimeLimitException;

class OperationTimeLimitHandler implements ErrorHandlerInterface
{
    public const USLEEP_DEFAULT = 5000000;
    private int $usleep;
    private bool $isHandlingEnabled;
    private const ERROR_TEMPLATE = [
        "error" => "OPERATION_TIME_LIMIT",
        "error_description" => "Method is blocked due to operation time limit."
    ];

    public function __construct(bool $isHandlingEnabled, int $usleep = self::USLEEP_DEFAULT)
    {
        $this->isHandlingEnabled = $isHandlingEnabled;
        $this->usleep = $usleep;
    }

    public function canHandle(ErrorContext $errorContext): bool
    {
        if ($errorContext->response['error'] === self::ERROR_TEMPLATE['error'] &&
            $errorContext->response['error_description'] === self::ERROR_TEMPLATE['error_description']) {
            return true;
        }

        return false;
    }

    public function handle(ErrorContext $errorContext): bool
    {
        if (! $this->isHandlingEnabled) {
            throw new OperationTimeLimitException(json_encode($errorContext->response, JSON_UNESCAPED_UNICODE));
        }

        usleep($this->usleep);
        return true;
    }
}
