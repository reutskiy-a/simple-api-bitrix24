<?php

declare(strict_types=1);

namespace SimpleApiBitrix24\Connectors\Traits;

use Psr\Http\Message\ResponseInterface;
use SimpleApiBitrix24\Constants\AppConstants;

trait ConnectorTrait
{
    private function buildBatchQueries(array $queries): array
    {
        $httpQuery = [];
        foreach($queries as $key => $query) {
            $httpQuery[] = $query['method'] . '?' . http_build_query($query['params']);
        }
        return $httpQuery;
    }

    private function makeHttpRequest(string $url, array $data): ResponseInterface
    {
        return $this->httpClient->post($url, [
            'http_errors' => false,
            'verify' => false,
            'headers' => [
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
                'User-Agent' => AppConstants::APP_INFO
            ],
            'json' => $data
        ]);
    }
}
