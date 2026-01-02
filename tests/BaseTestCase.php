<?php

declare(strict_types=1);

namespace SimpleApiBitrix24\Tests;

use Carbon\CarbonImmutable;
use Dotenv\Dotenv;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use PDO;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use ReflectionException;
use SimpleApiBitrix24\ApiClientBitrix24;
use SimpleApiBitrix24\DatabaseCore\Models\User;


abstract class BaseTestCase extends TestCase
{
    public function __construct(string $name)
    {
        parent::__construct($name);

        $dotenv = Dotenv::createImmutable(__DIR__ . '/', '.env.testing');
        $dotenv->load();
    }

    /**
     * to obtain a PDO object using data from the .env.testing file
     *
     * @param string $driver
     * @return PDO
     */
    protected function createPdo(string $driver): PDO
    {
        switch ($driver) {
            case 'pgsql':
                $dsn = sprintf(
                    '%s:host=%s;port=%s;dbname=%s',
                    $_ENV['DB_DRIVER_PGSQL'],
                    $_ENV['DB_HOST_PGSQL'],
                    $_ENV['DB_PORT_PGSQL'],
                    $_ENV['DB_DATABASE_PGSQL']
                );
                return new PDO(
                    $dsn, $_ENV['DB_USER_PGSQL'], $_ENV['DB_PASSWORD_PGSQL'],
                    [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
                );

            case 'mysql':
                $dsn = sprintf(
                    '%s:host=%s;port=%s;dbname=%s;charset=utf8mb4',
                    $_ENV['DB_DRIVER_MYSQL'],
                    $_ENV['DB_HOST_MYSQL'],
                    $_ENV['DB_PORT_MYSQL'],
                    $_ENV['DB_DATABASE_MYSQL']
                );
                return new PDO(
                    $dsn, $_ENV['DB_USER_MYSQL'], $_ENV['DB_PASSWORD_MYSQL'],
                    [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
                );

            case 'sqlite':
                return new PDO(
                    "{$_ENV['DB_DRIVER_SQLITE']}:{$_ENV['DB_DATABASE_SQLITE']}", null, null,
                    [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
                );

            default:
                throw new \InvalidArgumentException("Unsupported driver: $driver");
        }
    }

    /**
     * to obtain a test user object
     *
     * @param int|null $userId
     * @return User
     */
    protected function getUserObject(int|null $userId = null): User
    {
        return new User(
            $userId ?? filter_var($_ENV['USER_ID'], FILTER_VALIDATE_INT),
            $_ENV['USER_MEMBER_ID'],
            filter_var($_ENV['USER_IS_ADMIN'], FILTER_VALIDATE_BOOLEAN),
            $_ENV['USER_AUTH_TOKEN'],
            $_ENV['USER_REFRESH_TOKEN'],
            $_ENV['USER_DOMAIN'],
            $_ENV['USER_CLIENT_ID'],
            $_ENV['USER_CLIENT_SECRET'],
            new CarbonImmutable($_ENV['USER_CREATED_AT']),
            new CarbonImmutable($_ENV['USER_UPDATED_AT'])
        );
    }

    protected function dropTable(PDO $pdo, string $tableName): int|false
    {
        $sql = "DROP TABLE IF EXISTS " . $tableName;
        return $pdo->exec($sql);
    }

    protected function getGuzzleHttpClientMock(array $response, string|int $httpStatus = 200): Client
    {
        $mock = new MockHandler([
            new Response($httpStatus, ['Content-Type' => 'application/json'], json_encode($response))
        ]);

        $handlerStack = HandlerStack::create($mock);
        return new Client(['handler' => $handlerStack]);
    }

    /**
     * @param array $queue
     * example:
     * [
     *      new GuzzleHttp\Psr7\Response(200, ['Content-Type' => 'application/json'], json_encode(["data" => 1234])),
     *      new GuzzleHttp\Psr7\Response(200, [], json_encode(["data" => 1234]))
     * ]
     * @return Client
     */
    protected function getGuzzleHttpClientMockQueue(array $queue): Client
    {
        $mock = new MockHandler($queue);

        $handlerStack = HandlerStack::create($mock);
        return new Client(['handler' => $handlerStack]);
    }

    /**
     * to substitute a mocked http client in the api client object
     *
     * @param Client $mockedHttpClient
     * @param ApiClientBitrix24 $api
     * @return void
     * @throws ReflectionException
     */
    protected function setMockedHttpClientInApiClient(Client $mockedHttpClient, ApiClientBitrix24 $api): void
    {
        $reflectionApi = new ReflectionClass($api);
        $connectorProperty = $reflectionApi->getProperty('connector');
        $connector = $connectorProperty->getValue($api);

        $reflectionConnector = new ReflectionClass($connector);
        $httpClientProperty = $reflectionConnector->getProperty('httpClient');
        $httpClientProperty->setValue($connector, $mockedHttpClient);
    }
}
