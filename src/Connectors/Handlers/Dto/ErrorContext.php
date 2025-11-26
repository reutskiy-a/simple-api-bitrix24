<?php

declare(strict_types=1);

namespace SimpleApiBitrix24\Connectors\Handlers\Dto;

use SimpleApiBitrix24\DatabaseCore\Models\User;

final class ErrorContext
{
    public function __construct(
        public readonly array $response,
        public readonly ?User $user = null
    ) {

    }
}
