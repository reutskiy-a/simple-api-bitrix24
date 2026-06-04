<?php

declare(strict_types=1);

namespace SimpleApiBitrix24\Services;

use SimpleApiBitrix24\DTOs\BatchItem;

class BatchCollector
{
    private array $items = [];

    public function __construct(
        private Batch $batch
    ) {}

    public function add(string $method, array $params = []): self
    {
        $this->items[] = [
            'method' => $method,
            'params' => $params,
        ];

        return $this;
    }

    public function addByDto(BatchItem $item): self
    {
        $this->items[] = $item->toArray();
        return $this;
    }

    public function items(): array
    {
        return $this->items;
    }

    public function execute(): array
    {
        $result = $this->batch->call($this->items);
        $this->items = [];

        return $result;
    }
}
