<?php

declare(strict_types=1);

namespace SimpleApiBitrix24\Connectors;

use GuzzleHttp\Client;
use SimpleApiBitrix24\Connectors\Managers\ErrorResponseManager;
use SimpleApiBitrix24\Connectors\Services\ApiLimitService;
use SimpleApiBitrix24\Connectors\Traits\ConnectorTrait;
use SimpleApiBitrix24\Exceptions\ConnectorException;

class WebhookConnector implements ConnectorInterface
{
    use ConnectorTrait;

    private string|null $webhook;
    private ErrorResponseManager $errorResponseManager;
    private Client $httpClient;

    public function __construct(string $webhook, ErrorResponseManager $errorResponseManager)
    {
        $this->webhook = $webhook;
        $this->errorResponseManager = $errorResponseManager;
        $this->httpClient = new Client();
    }

    /**
     * @throws ConnectorException
     */
    public function sendRequest(string $method, array $params): array
    {
        $this->assertValidCredentials($this->webhook);

        $url = $this->webhook . $method . ".json";
        $response = $this->makeHttpRequest($url, $params);
        $response = json_decode($response->getBody()->getContents(), true);

        if ($this->errorResponseManager->shouldTheRequestBeRepeated($response)) {
            return $this->sendRequest($method, $params);
        }

        return $response;
    }

    /**
     * @throws ConnectorException
     */
    public function sendBatchRequest(array $queries): array
    {
        $this->assertValidCredentials($this->webhook);

        $url = $this->webhook . "batch.json";
        $httpQuery = $this->buildBatchQueries($queries);
        $data = ['cmd' => $httpQuery, 'halt' => 0];

        $response = $this->makeHttpRequest($url, $data);
        $response = json_decode($response->getBody()->getContents(), true);

        if ($this->errorResponseManager->shouldTheRequestBeRepeated($response)) {
            return $this->sendBatchRequest($queries);
        }

        return $response;
    }

    /**
     * @throws ConnectorException
     */
    private function assertValidCredentials($webhook): bool
    {
        if (filter_var($webhook, FILTER_VALIDATE_URL)) {
            return true;
        }

        throw new ConnectorException("the webhook is incorrect: '$webhook'");
    }
}
