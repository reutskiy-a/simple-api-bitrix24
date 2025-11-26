<?php

namespace SimpleApiBitrix24\Connectors\Models;

final class Webhook
{
    public function __construct(
        private string $url
    ) {

    }

    public function getUrl(): string
    {
        return $this->url;
    }
}
