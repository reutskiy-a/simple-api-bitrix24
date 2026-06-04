<?php

declare(strict_types=1);

namespace SimpleApiBitrix24\DTOs;

class BatchItem
{
    public function __construct(
        public readonly string $method,
        public readonly array $params = []
    ) {

    }

    public function toArray(): array
    {
        return [
            'method' => $this->method,
            'params' => $this->params,
        ];
    }
}
