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

    public function add(BatchItem $batchItem): self
    {
        $this->items[] = $batchItem;

        return $this;
    }

    public function items(): array
    {
        return $this->items;
    }

    public function execute(): array
    {
        $queries = [];
        /** @var BatchItem $item */
        foreach ($this->items as $item) {
            $queries[] = [
                'method' => $item->method,
                'params' => $item->params,
            ];
        }

        $result = $this->batch->call($queries);
        $this->items = [];

        return $result;
    }
}
