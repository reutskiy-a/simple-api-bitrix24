<?php

declare(strict_types=1);

namespace SimpleApiBitrix24\Connectors\Interfaces;

use SimpleApiBitrix24\Connectors\Handlers\Dto\ErrorContext;

interface ErrorHandlerInterface
{
    public function canHandle(ErrorContext $errorContext): bool;
    public function handle(ErrorContext $errorContext): bool;
}
