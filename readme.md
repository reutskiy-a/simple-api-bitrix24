![Unit Tested](https://img.shields.io/badge/Unit%20Tests-Passing-brightgreen)
![Integration Tests](https://img.shields.io/badge/Integration%20Tests-Passing-brightgreen)
![License](https://img.shields.io/github/license/reutskiy-a/simple-api-bitrix24)

> Simple REST API Bitrix24 client: OAuth 2.0, Webhook, flexible DB support, app installer, REST API Limit Handling Service

## Клиент для REST API Bitrix24:
### OAuth 2.0 (с автообновлением токенов), Webhook, поддержка всех популярных реляционных БД, менеджер установки локальных/тиражных приложений. Установка одного локального приложения на несколько порталов.
### Встроенная обработка лимитов REST API

## Installation
```bash
composer require reutskiy-a/simple-api-bitrix24
```

### LANGUAGE:
> [Русский](#русский)
> 
> [English](#english)
---

Local/distributed app installation example:

![Installation-demo](https://raw.githubusercontent.com/reutskiy-a/assets/main/api-client-bitrix24-local-app-installation.gif)

### Русский:

## Содержание:
1. [Быстрый старт: Webhook соединение](#1-быстрый-старт-webhook-соединение)

2. [Соединение OAuth 2.0 (Локальное или Тиражное приложение)](#2-соединение-oauth-20-локальное-или-тиражное-приложение)
   
    2.1. [Подготовка базы данных для хранения токенов](#21-подготовка-базы-данных-для-хранения-токенов)

    2.2. [Создание объекта соединения OAuth 2.0](#22-создание-объекта-соединения-oauth-20)
         
    2.3. [Установка приложения](#23-установка-приложения)

3. [Смена соединения или клонирование объекта соединения](#3-смена-соединения-или-клонирование-объекта-соединения)

4. [Логирование](#4-логирование)
   
    4.1. [Debug логирование](#41-debug-логирование)
   
    4.2. [Рекомендуемый уровень логирования](#42-рекомендуемый-уровень-логирования)

5. [Пакетные запросы](#5-пакетные-запросы)

    5.1 [Стандартный пакетный запрос](#51-стандартный-пакетный-запрос)

    5.2 [Сервис списочных методов для получения всех элементов](#52-сервис-списочных-методов-для-получения-всех-элементов)

6. [Встроенный сервис обработки лимитов REST API](#6-встроенный-сервис-обработки-лимитов-rest-api)
    


## 1. Быстрый старт Webhook соединение
```php
    use SimpleApiBitrix24\ApiClientSettings;
    use SimpleApiBitrix24\ApiClientBitrix24;

    $apiSettings = new ApiClientSettings();
    $apiSettings->setWebhookAuthEnabled(true)
                ->setDefaultConnection('https://portal.bitrix24.ru/rest/1/cj03r****1wbeg/');

    $api = new ApiClientBitrix24($apiSettings);
    
    $result = $api->call('crm.deal.get', ['ID' => 1]);
```



## 2. Соединение OAuth 2.0 (Локальное или Тиражное приложение)
> Вы можете устанавливать одно и тоже локальное приложение на разные порталы. 
> Только следите за корректностью client_id и client_secret при установке, 
> иначе токены не обновятся, когда их время жизни закончится, и приложение выбросит исключение.



### 2.1 Подготовка базы данных для хранения токенов
> Используйте любую удобную базу данных:
- PostgreSQL
- MySQL
- SQLite
- SQLServer

Создайте таблицу в базе данных. Пример запроса для MySQL:
```sql
    CREATE TABLE api_tokens_bitrix24(
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    member_id VARCHAR(255) UNIQUE NOT NULL,
    access_token VARCHAR(255) NOT NULL,
    expires_in VARCHAR(255) NOT NULL,
    application_token VARCHAR(255) NOT NULL,
    refresh_token VARCHAR(255) NOT NULL,
    domain VARCHAR(255) NOT NULL,
    client_endpoint VARCHAR(255) NOT NULL,
    client_id VARCHAR(255) NOT NULL,
    client_secret VARCHAR(255) NOT NULL
    );
```



### 2.2 Создание объекта соединения OAuth 2.0

```php
    use SimpleApiBitrix24\ApiDatabaseConfig;
    use SimpleApiBitrix24\ApiClientSettings;
    use SimpleApiBitrix24\ApiClientBitrix24;

    $pdo = new PDO('mysql:host=172.17.0.1;port=3306;dbname=bitrix24', 'root', 'password'); // Ваши настройки подключения к базе

    $databaseConfig = new ApiDatabaseConfig(
        pdo: $pdo,
        tableName: 'api_tokens_bitrix24',
        primaryKeyColumnName: 'id',
        memberIdColumnName: 'member_id',
        accessTokenColumnName: 'access_token',
        expiresInColumnName: 'expires_in',
        applicationTokenColumnName: 'application_token',
        refreshTokenColumnName: 'refresh_token',
        domainColumnName: 'domain',
        clientEndpointColumnName: 'client_endpoint',
        clientIdColumnName: 'client_id',
        clientSecretColumnName: 'client_secret'
    );

    $apiSettings = new ApiClientSettings();
    $apiSettings->setTokenAuthEnabled(true)
                ->setDefaultConnection('your_member_id');

    $api = new ApiClientBitrix24($apiSettings, $databaseConfig);

    $result = $api->call('crm.deal.get', ['ID' => 1]);
```

Если надо динамически устанавливать соединение к порталу на входящий $_REQUEST['member_id'], то делайте так:
```php
    // ...
    
    $apiSettings = new ApiClientSettings();
    $apiSettings->setTokenAuthEnabled(true);
    
    $api = new ApiClientBitrix24($apiSettings, $databaseConfig);
    $api->connectTo($_REQUEST['member_id']);
    
    $result = $api->call('crm.deal.get', ['ID' => 1]);
```


### 2.3 Установка приложения

```php
    use SimpleApiBitrix24\Services\Installation\InstallationService;
    
    // старт установки (добавление пользователя в базу данных)
    $installationService = new InstallationService();
    $installationService->startInstallation(
        'local.67c9b****83.1668***79',                              // client id
        '7KriLM5****T6tCgVSqUj2ILZFms5*****keBzYbzqso',             // client secret
        $databaseConfig,                                            // SimpleApiBitrix24\ApiDatabaseConfig, пример создания объекта выше
        $_REQUEST
    );
    
    // тут логика установки приложения, если требуется.
    $api->connectTo($_REQUEST['member_id']);                        // SimpleApiBitrix24\ApiClientBitrix24, пример создания объекта выше 
    $result = $api->call('scope');

    // завершение установки
    $installationService->finishInstallation();                     // перезагрузка страницы на index
```


## 3. Смена соединения или клонирование объекта соединения

Смена/установка соединения
```php
    $api->connectTo('member_id__or__webhook_url');
```
Клонирование объекта соединения, если нужно работать одновременно с разными порталами Битрикс24.
```php
    $secondApi = clone $firstApi;                                   // объект SimpleApiBitrix24\ApiClientBitrix24
    $secondApi->connectTo('new_member_id__or__webhook_url');        // получаем второй объект с соединением к другому порталу
```



## 4. Логирование

### 4.1 Debug логирование
При уровне логирования DEBUG, будут логироваться все запросы и ответы.

```php
    use Monolog\Formatter\LineFormatter;
    use Monolog\Handler\RotatingFileHandler;
    use Monolog\Logger;

    $logger = new Logger('api-b24');
        
    $handler = new RotatingFileHandler(
        '/var/www/poject/storage/logs/api-b24.log',                     // ваша путь для логов
        5,
    Logger::DEBUG
    );
    
    $formatter = new LineFormatter(
        "[%datetime%] %level_name%: %message% %context%\n",
        'Y-m-d H:i:s',
        true
    );
    $formatter->setJsonPrettyPrint(true);
    $handler->setFormatter($formatter);
    $logger->pushHandler($handler);

    $api = new ApiClientBitrix24($apiSettings, null, $logger);          // SimpleApiBitrix24\ApiClientBitrix24, пример создания объекта выше
```

### 4.2 Рекомендуемый уровень логирования
Рекомендуемый уровень логирования WARNING. 
В логи попадут только ответы сервера Bitrix24 с ошибками, или исключения этого пакета SimpleApiBitrix24.
```php
    // ...
    
    $handler = new RotatingFileHandler(
        '/var/www/poject/storage/logs/api-b24.log',                     // ваша путь для логов
        5,
    Logger::WARNING
    );
    
    // ...

```

## 5. Пакетные запросы

### 5.1 Стандартный пакетный запрос

> В один пакетный запрос можно завернуть до 50 запросов.
> 
> https://apidocs.bitrix24.ru/api-reference/how-to-call-rest-api/batch.html


```php
    $result = $api->callBatch([
        [
            'method' => 'crm.deal.get',
            'params' => ['id' => 1]
        ],
        [
            'method' => 'tasks.deal.get',
            'params' => ['id' => 2]
        ],
    ]);
```

### 5.2 Сервис списочных методов для получения всех элементов

SimpleApiBitrix24\Services\Batch::getAll() работает только с методами списочного типа. Возвращает все элементы указанной сущости.
```php
    use SimpleApiBitrix24\Services\Batch;
    
    $batchService = new Batch($api);                   // $api объект SimpleApiBitrix24\ApiClientBitrix24
    $tasks = $batchService->getAll('tasks.task.list', ['filter' => ['STATUS' => 5]]);
```

### 6. Встроенный сервис обработки лимитов REST API
> Подробней о лимитах rest api смотрите в официальной документации
> 
> https://apidocs.bitrix24.ru/limits.html

Этот пакет может обрабатывать ответы об ошибках лимитов REST API:

Пример обрабатываемых ошибок от сервера REST API:
```json
{
    "error": "QUERY_LIMIT_EXCEEDED",
    "error_description": "Too many requests"
}
```
```json
{
  "error": "OPERATION_TIME_LIMIT",
  "error_description": "Method is blocked due to operation time limit."
}
```

Включается обработка ошибок конфигурацией объекта SimpleApiBitrix24\ApiClientSettings
```php
use SimpleApiBitrix24\ApiClientSettings;

$apiSettings = new ApiClientSettings();
$apiSettings->setTokenAuthEnabled(true)
            ->setDefaultConnection('your_member_id')
            ->setQueryLimitExceededService(handleEnabled: true, usleep: 500000)
            ->setOperationTimeLimitService(handleEnabled: true, usleep: 5000000);
```
По умолчанию обработка этих ошибок отключена, при включении укажите время ожидания в микросекундах.
При получении ответа с одной из этих ошибок API Client будет делать повторный запрос через заданный интервал времени,
не останавливая работу скрипта, делать это будет постоянно пока не завершится выполнение скрипта или 
не закончится время жизни приложения.

---
# English

### Client for Bitrix24 REST API:
### OAuth 2.0 (with automatic token refresh), Webhook, support for all popular relational databases, manager for installing local/distributed applications. Installation of a single local application across multiple portals.

## Table of Contents:
1. [Quick Start: Webhook Connection](#1-quick-start-webhook-connection)

2. [OAuth 2.0 Connection (Local or Edition App)](#2-oauth-20-connection-local-or-edition-app))

   2.1. [Preparing the Database for Token Storage](#21-preparing-the-database-for-token-storage)

   2.2. [Creating an OAuth 2.0 Connection Object](#22-creating-an-oauth-20-connection-object)

   2.3. [App Installation](#23-app-installation)

3. [Switching or Cloning the Connection](#3-switching-or-cloning-the-connection)

4. [Logging](#4-logging)

   4.1. [Debug Logging](#41-debug-logging)

   4.2. [Recommended Logging Level](#42-recommended-logging-level)

5. [Batch Requests](#5-batch-requests)

   5.1 [Standard Batch Request](#51-standard-batch-request)

   5.2 [Service for List Methods to Retrieve All Items](#52-service-for-list-methods-to-retrieve-all-items)

6. [Built-in REST API Limit Handling Service](#6-built-in-rest-api-limit-handling-service)

## 1. Quick Start: Webhook Connection
```php
    use SimpleApiBitrix24\ApiClientSettings;
    use SimpleApiBitrix24\ApiClientBitrix24;

    $apiSettings = new ApiClientSettings();
    $apiSettings->setWebhookAuthEnabled(true)
                ->setDefaultConnection('https://portal.bitrix24.ru/rest/1/cj03r****1wbeg/');

    $api = new ApiClientBitrix24($apiSettings);
    
    $result = $api->call('crm.deal.get', ['ID' => 1]);
```

## 2. OAuth 2.0 Connection (Local or Edition App
> You can install the same local app on different portals.
> Ensure client_id and client_secret are correct during installation,
> otherwise tokens won’t refresh when they expire, and the app will throw an exception.



### 2.1 Preparing the Database for Token Storage
Use any database of your choice:
- PostgreSQL
- MySQL
- SQLite
- SQLServer

Create a table in the database. Example query for MySQL:
```sql
    CREATE TABLE api_tokens_bitrix24(
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    member_id VARCHAR(255) UNIQUE NOT NULL,
    access_token VARCHAR(255) NOT NULL,
    expires_in VARCHAR(255) NOT NULL,
    application_token VARCHAR(255) NOT NULL,
    refresh_token VARCHAR(255) NOT NULL,
    domain VARCHAR(255) NOT NULL,
    client_endpoint VARCHAR(255) NOT NULL,
    client_id VARCHAR(255) NOT NULL,
    client_secret VARCHAR(255) NOT NULL
    );
```



### 2.2 Creating an OAuth 2.0 Connection Object

```php
    use SimpleApiBitrix24\ApiDatabaseConfig;
    use SimpleApiBitrix24\ApiClientSettings;
    use SimpleApiBitrix24\ApiClientBitrix24;

    $pdo = new PDO('mysql:host=172.17.0.1;port=3306;dbname=bitrix24', 'root', 'password'); // Your database connection settings

    $databaseConfig = new ApiDatabaseConfig(
        pdo: $pdo,
        tableName: 'api_tokens_bitrix24',
        primaryKeyColumnName: 'id',
        memberIdColumnName: 'member_id',
        accessTokenColumnName: 'access_token',
        expiresInColumnName: 'expires_in',
        applicationTokenColumnName: 'application_token',
        refreshTokenColumnName: 'refresh_token',
        domainColumnName: 'domain',
        clientEndpointColumnName: 'client_endpoint',
        clientIdColumnName: 'client_id',
        clientSecretColumnName: 'client_secret'
    );

    $apiSettings = new ApiClientSettings();
    $apiSettings->setTokenAuthEnabled(true)
                ->setDefaultConnection('your_member_id');

    $api = new ApiClientBitrix24($apiSettings, $databaseConfig);

    $result = $api->call('crm.deal.get', ['ID' => 1]);
```

To dynamically set the connection based on $_REQUEST['member_id'], do this:
```php
    // ...
    
    $apiSettings = new ApiClientSettings();
    $apiSettings->setTokenAuthEnabled(true);
    
    $api = new ApiClientBitrix24($apiSettings, $databaseConfig);
    $api->connectTo($_REQUEST['member_id']);
    
    $result = $api->call('crm.deal.get', ['ID' => 1]);
```


### 2.3 App Installation

```php
    use SimpleApiBitrix24\Services\Installation\InstallationService;
    
    // Start installation (add user to the database)
    $installationService = new InstallationService();
    $installationService->startInstallation(
        'local.67c9b****83.1668***79',                              // client id
        '7KriLM5****T6tCgVSqUj2ILZFms5*****keBzYbzqso',             // client secret
        $databaseConfig,                                            // SimpleApiBitrix24\ApiDatabaseConfig, see creation example above
        $_REQUEST
    );
    
    // Add your app installation logic here, if needed
    $api->connectTo($_REQUEST['member_id']);                        // SimpleApiBitrix24\ApiClientBitrix24, see creation example above
    $result = $api->call('scope');

    // Finish installation
    $installationService->finishInstallation();                     // Reloads the page to index
```




## 3. Switching or Cloning the Connection

Switching/setting a connection:
```php
    $api->connectTo('member_id__or__webhook_url');                  // SimpleApiBitrix24\ApiClientBitrix24 object
```
Cloning the connection object to work with multiple Bitrix24 portals simultaneously:
```php
    $secondApi = clone $firstApi;                                   // SimpleApiBitrix24\ApiClientBitrix24 object
    $secondApi->connectTo('new_member_id__or__webhook_url');        // Creates a second object connected to another portal
```



## 4. Logging

### 4.1 Debug Logging
At the DEBUG logging level, all requests and responses will be logged.

```php
    use Monolog\Formatter\LineFormatter;
    use Monolog\Handler\RotatingFileHandler;
    use Monolog\Logger;

    $logger = new Logger('api-b24');
        
    $handler = new RotatingFileHandler(
        '/var/www/poject/storage/logs/api-b24.log',                     // Your log file path
        5,
    Logger::DEBUG
    );
    
    $formatter = new LineFormatter(
        "[%datetime%] %level_name%: %message% %context%\n",
        'Y-m-d H:i:s',
        true
    );
    $formatter->setJsonPrettyPrint(true);
    $handler->setFormatter($formatter);
    $logger->pushHandler($handler);

    $api = new ApiClientBitrix24($apiSettings, null, $logger);          // SimpleApiBitrix24\ApiClientBitrix24, see creation example above
```

### 4.2 Recommended Logging Level
The recommended logging level is WARNING.

Only Bitrix24 server error responses or exceptions from this SimpleApiBitrix24 package will be logged.
```php
    // ...
    
    $handler = new RotatingFileHandler(
        '/var/www/poject/storage/logs/api-b24.log',                     // Your log file path
        5,
    Logger::WARNING
    );
    
    // ...

```

## 5. Batch Requests

### 5.1 Standard Batch Request

> Up to 50 requests can be included in a single batch request.
> 
> https://apidocs.bitrix24.com/api-reference/how-to-call-rest-api/batch.html


```php
    $result = $api->callBatch([
        [
            'method' => 'crm.deal.get',
            'params' => ['id' => 1]
        ],
        [
            'method' => 'tasks.deal.get',
            'params' => ['id' => 2]
        ],
    ]);
```

### 5.2 Service for List Methods to Retrieve All Items

SimpleApiBitrix24\Services\Batch::getAll() works only with list-type methods and retrieves all items of the specified entity.
```php
    use SimpleApiBitrix24\Services\Batch;
    
    $batchService = new Batch($api);                   // $api is an instance of SimpleApiBitrix24\ApiClientBitrix24
    $tasks = $batchService->getAll('tasks.task.list', ['filter' => ['STATUS' => 5]]);
```

### 6. Built-in REST API Limit Handling Service
> For more details on REST API limits, refer to the official documentation:
>
> https://apidocs.bitrix24.com/limits.html

This package can handle REST API limit error responses:

Examples of handled REST API server errors:
```json
{
    "error": "QUERY_LIMIT_EXCEEDED",
    "error_description": "Too many requests"
}
```
```json
{
    "error": "OPERATION_TIME_LIMIT",
    "error_description": "Method is blocked due to operation time limit."
}
```
Error handling is enabled through the configuration of the SimpleApiBitrix24\ApiClientSettings object:

```php
use SimpleApiBitrix24\ApiClientSettings;

$apiSettings = new ApiClientSettings();
$apiSettings->setTokenAuthEnabled(true)
            ->setDefaultConnection('your_member_id')
            ->setQueryLimitExceededService(handleEnabled: true, usleep: 500000)
            ->setOperationTimeLimitService(handleEnabled: true, usleep: 5000000);
```
By default, handling of these errors is disabled. When enabled, specify the wait time in microseconds.
Upon receiving one of these error responses, the API client will retry the request after the specified time interval
without stopping the script's execution. It will continue doing so until the script completes or
the application's lifetime expires.
