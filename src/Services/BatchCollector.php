<?php

declare(strict_types=1);

namespace SimpleApiBitrix24\Services;

class BatchCollector
{
    private array $queries = [];

    public function __construct(
        private Batch $batch
    ) {}

    public function add(string $method, array $params = []): self
    {
        $this->queries[] = [
            'method' => $method,
            'params' => $params,
        ];

        return $this;
    }

    public function queries(): array
    {
        return $this->queries;
    }

    public function execute(): array
    {
        $result = $this->batch->call($this->queries);

        $this->queries = [];

        return $result;
    }
}
