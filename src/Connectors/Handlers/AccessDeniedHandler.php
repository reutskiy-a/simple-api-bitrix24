<?php

declare(strict_types=1);

namespace SimpleApiBitrix24\Connectors\Handlers;

use SimpleApiBitrix24\Connectors\Handlers\Dto\ErrorContext;
use SimpleApiBitrix24\Connectors\Interfaces\ErrorHandlerInterface;
use SimpleApiBitrix24\Exceptions\AccessDeniedException;

class AccessDeniedHandler implements ErrorHandlerInterface
{
    private const ERROR_TEMPLATE = [
        "error" => "",
        "error_description" => "Access denied."
    ];

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
        throw new AccessDeniedException(json_encode($errorContext->response, JSON_UNESCAPED_UNICODE));
        return false;
    }
}
