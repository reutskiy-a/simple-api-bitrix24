<?php

declare(strict_types=1);

namespace SimpleApiBitrix24\Connectors\Handlers;

use SimpleApiBitrix24\Connectors\Handlers\Dto\ErrorContext;
use SimpleApiBitrix24\Connectors\Interfaces\ErrorHandlerInterface;
use SimpleApiBitrix24\Exceptions\Bitrix24ResponseException;

class DefaultErrorHandler implements ErrorHandlerInterface
{
    public function canHandle(ErrorContext $errorContext): bool
    {
        if (array_key_exists("error", $errorContext->response) &&
            array_key_exists("error_description", $errorContext->response)) {
            return true;
        }

            return false;
    }

    public function handle(ErrorContext $errorContext): bool
    {
        throw new Bitrix24ResponseException(json_encode($errorContext->response, JSON_UNESCAPED_UNICODE));
        return false;
    }
}
