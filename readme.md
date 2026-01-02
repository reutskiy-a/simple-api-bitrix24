![Unit Tested](https://img.shields.io/badge/Unit%20Tests-Passing-brightgreen)
![Integration Tests](https://img.shields.io/badge/Integration%20Tests-Passing-brightgreen)
![License](https://img.shields.io/github/license/reutskiy-a/simple-api-bitrix24)

> REST API клиент для облачной версии Битрикс24
```bash
composer require reutskiy-a/simple-api-bitrix24
```

lang: [Русский](#надо-ли-вам-использовать-этот-rest-api-клиент) / [English](#should-you-use-this-rest-api-client)


## Этот REST API клиент подойдёт тем, кому нужен быстрый старт, минимализм и вся необходимая функциональность для разработки полноценных приложений для облачной версии Битрикс24.

### Что умеет:
- авторизация через Webhook и OAuth 2.0
- автоматическая работа с любой реляционной БД (MySQL, PostgreSQL, SQLite): быстрое создание таблицы пользователей и сохранение данных через встроенный UserRepository.
- авто‑обновление пользовательских токенов.
- сохранение и использование токенов любых пользователей портала, работа с их правами доступа.
- обработка лимитов REST API — приложение не прерывает работу при ошибках Bitrix24.
- локальные приложения могут работать, как тиражные (одно приложение - много порталов).
- сервис установки приложений.
- логирование.
- знакомый подход для тех, кто работал с CRest.

Более детальную информацию и примеры по работе с rest api клиентом смотрите в содержании ниже.

Пример установки локального приложения:
![Installation-demo](https://raw.githubusercontent.com/reutskiy-a/assets/main/simple-api-bitrix24/simple_api_bitrix24_v2_local-app.gif)

## Содержание:
1. [Webhook авторизация - быстрый старт](#1-webhook-авторизация---быстрый-старт)

2. [OAuth 2.0 авторизация - быстрый старт](#2-oauth-20-авторизация---быстрый-старт)
    
   2.1 [Подготовка соединения с базой данных и создание таблицы пользователей](#21-подготовка-соединения-с-базой-данных-и-создание-таблицы-пользователей)
   
   2.2 [Создание объекта rest api клиента с OAuth 2.0 авторизацией](#22-создание-объекта-rest-api-клиента-с-oauth-20-авторизацией)
          
   2.3 [Установка приложения и сохранение данных пользователя в БД](#23-установка-приложения-и-сохранение-данных-пользователя-в-бд)

3. [Подробней о работе с rest api клиентом](#3-подробней-о-работе-с-rest-api-клиентом)
    
   3.1 [Установка авторизации для rest api клиента по умолчанию](#31-установка-авторизации-для-rest-api-клиента-по-умолчанию)
       
   3.2 [Смена авторизации rest api клиента / работа с разными порталами или пользователями одновременно](#32-смена-авторизации-rest-api-клиента--работа-с-разными-порталами-или-пользователями-одновременно)
          
   3.3 [Сохранение данных пользователя в бд](#33-сохранение-данных-пользователя-в-бд)
             
   3.4 [Работа с пользователями через UserRepository](#34-работа-с-пользователями-через-userrepository)

4. [Настройка обработки лимитов rest api bitrix24](#4-настройка-обработки-лимитов-rest-api-bitrix24)

5. [Логирование](#5-логирование)

6. [Batch](#6-batch)
   
   6.1 [Обычный batch запрос](#61-обычный-batch-запрос)

   6.2 [Batch сервис](#62-batch-сервис)



## 1. Webhook авторизация - быстрый старт

```php
use SimpleApiBitrix24\ApiClientBitrix24;
use SimpleApiBitrix24\ApiClientSettings;
use SimpleApiBitrix24\Connectors\Models\Webhook;
use SimpleApiBitrix24\Enums\AuthType;

$apiSettings = new ApiClientSettings(AuthType::WEBHOOK);
$apiSettings->setDefaultCredentials(new Webhook('https://test.bitrix24.ru/rest/1/b1hw1*****k12t/'));

$api = new ApiClientBitrix24($apiSettings);

print_r($api->call('crm.deals.get', ['ID' => 2]));
```


## 2. OAuth 2.0 авторизация - быстрый старт

### 2.1 Подготовка соединения с базой данных и создание таблицы пользователей
> Используйте любую удобную базу данных: MySQL, PostgreSQL, SQLite.

```php
use PDO;
use SimpleApiBitrix24\ApiDatabaseConfig;
use SimpleApiBitrix24\DatabaseCore\TableManager;

// Создаём объект PDO.
$pdo = new PDO('mysql:host=localhost;port=3306;dbname=test', 'root', 'password');

// Создаём объект описывающий схему будущей таблицы.
// Если вам нужно указать свои названия колонок и самой таблицы, то задайте их через ApiDatabaseConfig::__construct();
$databaseConfig = ApiDatabaseConfig::build($pdo);

// Создаём таблицу в базе, если она там отсутствует.
$tableManager = new TableManager($databaseConfig);
$tableManager->createUsersTableIfNotExists();
```

### 2.2 Создание объекта rest api клиента с OAuth 2.0 авторизацией

```php
use PDO;
use SimpleApiBitrix24\ApiClientBitrix24;
use SimpleApiBitrix24\ApiClientSettings;
use SimpleApiBitrix24\ApiDatabaseConfig;
use SimpleApiBitrix24\DatabaseCore\UserRepository;
use SimpleApiBitrix24\Enums\AuthType;

$pdo = new PDO('mysql:host=localhost;port=3306;dbname=test', 'root', 'password');
$databaseConfig = ApiDatabaseConfig::build($pdo);   // или задайте свою схему таблицы через ApiDatabaseConfig::__construct()

// Создаём объект пользователя, авторизацию которого будем использовать.
$repository = new UserRepository($databaseConfig);
$user = $repository->getUserByIdAndMemberId(1, 'bitrix24_member_id');

// создаём объект настроек rest api клиента и зададим в этом примере авторизацию по умолчанию.
// так же авторизацию можно установить или изменить методом ApiClientBitrix24::setCredentials
$apiSettings = new ApiClientSettings(AuthType::TOKEN);
$apiSettings->setDefaultCredentials($user);

// создаём объект rest api клиента
$api = new ApiClientBitrix24($apiSettings, $databaseConfig);

print_r($api->call('crm.deals.get', ['ID' => 2]));
```

### 2.3 Установка приложения и сохранение данных пользователя в БД

```php
use PDO;
use SimpleApiBitrix24\ApiClientBitrix24;
use SimpleApiBitrix24\ApiClientSettings;
use SimpleApiBitrix24\ApiDatabaseConfig;
use SimpleApiBitrix24\DatabaseCore\UserRepository;
use SimpleApiBitrix24\Enums\AuthType;
use SimpleApiBitrix24\Services\Installation\InstallationService;

$pdo = new PDO('mysql:host=localhost;port=3306;dbname=test', 'root', 'password');
$databaseConfig = ApiDatabaseConfig::build($pdo);   // или задайте свою схему таблицы через ApiDatabaseConfig::__construct()

$apiSettings = new ApiClientSettings(AuthType::TOKEN);
$api = new ApiClientBitrix24($apiSettings, $databaseConfig);

// сохраняем данные пользователя в базу
$user = InstallationService::createUserFromProfileAndSave(
    $databaseConfig,
    'local.693c2b5c42e7c3.81926786',
    'fDlCI34BZbbWv31iNm7H1jpwmu5py9vMyMkkVzQ3IC3WQdPQC4',
    $_REQUEST['member_id'],
    $_REQUEST['AUTH_ID'],
    $_REQUEST['REFRESH_ID'],
    $_REQUEST['DOMAIN']
);

// Тут ваша логика установки приложения
$api->setCredentials($user); // устанавливаем авторизацию для rest api клиента
print_r($api->call('scope'));

// Вызываем метод завершения установки приложения
InstallationService::finishInstallation();
```

## 3. Подробней о работе с rest api клиентом

### 3.1 Установка авторизации для rest api клиента по умолчанию

```php
use SimpleApiBitrix24\ApiClientSettings;

$apiSettings = new ApiClientSettings(AuthType::TOKEN);
$apiSettings->setDefaultCredentials($user);
```

### 3.2 Смена авторизации rest api клиента / работа с разными порталами или пользователями одновременно

```php
use PDO;
use SimpleApiBitrix24\ApiClientBitrix24;
use SimpleApiBitrix24\ApiClientSettings;
use SimpleApiBitrix24\ApiDatabaseConfig;
use SimpleApiBitrix24\DatabaseCore\UserRepository;
use SimpleApiBitrix24\Enums\AuthType;
use SimpleApiBitrix24\Services\Installation\InstallationService;

$pdo = new PDO('mysql:host=localhost;port=3306;dbname=test', 'root', 'password');
$databaseConfig = ApiDatabaseConfig::build($pdo);   // или задайте свою схему таблицы через ApiDatabaseConfig::__construct()
$apiSettings = new ApiClientSettings(AuthType::TOKEN);

// Пользователь №1
$repository = new UserRepository($databaseConfig);
$user_1 = $repository->getUserByIdAndMemberId(1, 'bitrix24_member_id_1');

$apiClient_1 = new ApiClientBitrix24($apiSettings, $databaseConfig);
$apiClient_1->setCredentials($user_1);

// Пользователь №2
$user_2 = $repository->getFirstAdminByMemberId('bitrix24_member_id_2');

$apiClient_2 = clone $apiClient_1;
$apiClient_2->setCredentials($user_2);

// далее можем работать с разными независимыми друг от друга объектами rest api клиента
print_r($apiClient_1->call('profile'));
print_r($apiClient_2->call('profile'));
```

### 3.3 Сохранение данных пользователя в бд

Будьте внимательны передавая client_id и client_secret в методе InstallationService::createUserFromProfileAndSave,
они будут записаны в БД для каждого пользователя отдельно,
что позволяет работать локальным приложениям, как тиражным (одно приложение - много порталов).
Если вы укажете client_id и client_secret не верно для конкретного пользователя, то его токены авторизации не обновятся 
и rest api клиент выбросит исключение.
```php
use SimpleApiBitrix24\Services\Installation\InstallationService;

// метод добавляет или обновляет данные пользователя в базе
InstallationService::createUserFromProfileAndSave(
    $databaseConfig,
    'local.693c2b5c42e7c3.81926786',
    'fDlCI34BZbbWv31iNm7H1jpwmu5py9vMyMkkVzQ3IC3WQdPQC4',
    $_REQUEST['member_id'],
    $_REQUEST['AUTH_ID'],
    $_REQUEST['REFRESH_ID'],
    $_REQUEST['DOMAIN']
);
```

### 3.4 Работа с пользователями через UserRepository

```php
use PDO;
use SimpleApiBitrix24\ApiClientBitrix24;
use SimpleApiBitrix24\DatabaseCore\UserRepository;

$pdo = new PDO('mysql:host=localhost;port=3306;dbname=test', 'root', 'password');
$databaseConfig = ApiDatabaseConfig::build($pdo);   // или задайте свою схему таблицы через ApiDatabaseConfig::__construct()

// Все методы и описание смотрите в реализации репозитория
$repository = new UserRepository($databaseConfig);

$user = $repository->getFirstUserByMemberId('member_id');
$user = $repository->getFirstAdminByMemberId('member_id');

$users = $repository->getAllUsersByMemberId('member_id');

$repository->delete($user);
```

## 4. Настройка обработки лимитов rest api Bitrix24

> Подробнее о лимитах rest api смотрите в официальной документации
>
> https://apidocs.bitrix24.ru/limits.html

Этот rest api клиент может обрабатывать ответы об ошибках лимитов REST API:

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

По умолчанию обработка выше перечисленных ошибок включена в этом rest api клиенте.

Как работает обработка ошибок - при получении ответа с одной из ошибок API Client будет делать повторный запрос через заданный интервал времени,
не останавливая работу скрипта, делать это будет постоянно пока не завершится выполнение скрипта или
не истечёт время жизни приложения.

```php
use PDO;
use SimpleApiBitrix24\ApiClientBitrix24;
use SimpleApiBitrix24\ApiClientSettings;
use SimpleApiBitrix24\ApiDatabaseConfig;
use SimpleApiBitrix24\Enums\AuthType;

$pdo = new PDO('mysql:host=localhost;port=3306;dbname=test', 'root', 'password');
$databaseConfig = ApiDatabaseConfig::build($pdo);   // или задайте свою схему таблицы через ApiDatabaseConfig::__construct()

// по умолчанию обработка лимитов всегда включена при создании объекта ApiClientSettings;
// вы можете отключить или задать свой интервал для повторного запроса в микросекундах
$apiSettings = new ApiClientSettings(AuthType::TOKEN);
$apiSettings
    ->setOperationTimeLimitHandler(true, 5000000)
    ->setQueryLimitExceededHandler(true, 1000000);

$api = new ApiClientBitrix24($apiSettings, $databaseConfig);
```

Так же rest api клиент обрабатывает по умолчанию ошибки:
- Крайне редко наблюдались пустые ответы от сервера rest api bitrix24, в случае такой ситуации клиент сделает повторный запрос.
- Обновление токенов авторизации и сохранение новых в базе данных, в случае получения ошибки об истечении срока жизни токенов.
После запрос повторится.

В остальных случаях клиент выбрасывает исключения на другие ответы с ошибками от сервера rest api bitrix24.


## 5. Логирование

При уровне логирования DEBUG, будут логироваться все запросы и ответы от сервера bitrix24.

При уровне логирования WARNING в логи попадут:
- если один из ключей Batch запроса получил ошибку от сервера bitrix24
- ответ с ошибкой от сервера bitrix24
- исключения этого клиента

```php
use Monolog\Formatter\LineFormatter;
use Monolog\Handler\RotatingFileHandler;
use Monolog\Logger;
use PDO;
use SimpleApiBitrix24\ApiClientBitrix24;
use SimpleApiBitrix24\ApiClientSettings;
use SimpleApiBitrix24\ApiDatabaseConfig;
use SimpleApiBitrix24\Enums\AuthType;

$pdo = new PDO('mysql:host=localhost;port=3306;dbname=test', 'root', 'password');
$databaseConfig = ApiDatabaseConfig::build($pdo);   // или задайте свою схему таблицы через ApiDatabaseConfig::__construct()
$apiSettings = new ApiClientSettings(AuthType::TOKEN);

$logger = new Logger('api-b24');
$handler = new RotatingFileHandler(
    storage_path('logs/rest-api-bitrix24.log'),
    15,
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

$api = new ApiClientBitrix24($apiSettings, $databaseConfig, $logger);
```

## 6. Batch

### 6.1 Обычный batch запрос

```php
use SimpleApiBitrix24\ApiClientBitrix24;

//...

$api = new ApiClientBitrix24($apiSettings, $databaseConfig);

$result = $api->callBatch([
    ['method' => 'scope', 'params' => []],
    ['method' => 'crm.deal.list', 'params' => ['select' => ['ID', 'TITLE']]],
]);

print_r($result);
```

### 6.2 Batch сервис

```php
use SimpleApiBitrix24\ApiClientBitrix24;
use SimpleApiBitrix24\Services\Batch;

// ...

$api = new ApiClientBitrix24($apiSettings, $databaseConfig);

$batchService = new Batch($api);

// получаем все элементы сущности, работает только со списочными методами
$getAllResult = $batchService->getAll('tasks.task.list', ['filter' => ['STATUS' => 5]]);

// вернёт ответы предварительно отсортировав их по тем же ключам массива, которые вы укажете в аргументе метода.
$resultWithKeys = $batchService->callWithKeys([
    'scope_response' => ['method' => 'scope', 'params' => []],
    'deal_list_response' => ['method' => 'crm.deal.list', 'params' => ['select' => ['ID', 'TITLE']]],
])
```



English:

## This REST API client is ideal for those who need a quick start, minimalism, and all the essential functionality for building full‑featured applications for the cloud version of Bitrix24.

### Features:
- authorization via Webhook and OAuth 2.0
- automatic integration with any relational database (MySQL, PostgreSQL, SQLite): fast user table creation and data persistence through the built‑in UserRepository
- automatic refresh of user access tokens
- storing and using tokens of any portal users, working with their access permissions
- REST API rate‑limit handling — the application does not stop when Bitrix24 returns errors
- local applications can operate as multi‑tenant apps (one application for many portals)
- application installation service
- logging
- familiar workflow for those who have used CRest

For more detailed information and usage examples, see the sections below.


## Table of Contents:
1. [Webhook Authorization — Quick Start](#1-webhook-authorization--quick-start)

2. [OAuth 2.0 Authorization — Quick Start](#2-oauth-20-authorization--quick-start)

   2.1 [Preparing the database connection and creating the users table](#21-preparing-the-database-connection-and-creating-the-users-table)

   2.2 [Creating a REST API client with OAuth 2.0 authorization](#22-creating-a-rest-api-client-with-oauth-20-authorization)

   2.3 [Application installation and saving user data to the database](#23-application-installation-and-saving-user-data-to-the-database)

3. [Detailed usage of the REST API client](#3-detailed-usage-of-the-rest-api-client)

   3.1 [Setting default authorization for the REST API client](#31-setting-default-authorization-for-the-rest-api-client)

   3.2 [Switching authorization / working with multiple portals or users simultaneously](#32-switching-authorization--working-with-multiple-portals-or-users-simultaneously)

   3.3 [Saving user data to the database](#33-saving-user-data-to-the-database)

   3.4 [Working with users via UserRepository](#34-working-with-users-via-userrepository)

4. [Configuring Bitrix24 REST API rate‑limit handling](#4-configuring-bitrix24-rest-api-ratelimit-handling)

5. [Logging](#5-logging)

6. [Batch](#6-batch)

   6.1 [Basic batch request](#61-basic-batch-request)

   6.2 [Batch service](#62-batch-service)


## 1. Webhook Authorization — Quick Start

```php
use SimpleApiBitrix24\ApiClientBitrix24;
use SimpleApiBitrix24\ApiClientSettings;
use SimpleApiBitrix24\Connectors\Models\Webhook;
use SimpleApiBitrix24\Enums\AuthType;

$apiSettings = new ApiClientSettings(AuthType::WEBHOOK);
$apiSettings->setDefaultCredentials(new Webhook('https://test.bitrix24.ru/rest/1/b1hw1*****k12t/'));

$api = new ApiClientBitrix24($apiSettings);

print_r($api->call('crm.deals.get', ['ID' => 2]));
```


## 2. OAuth 2.0 Authorization — Quick Start

### 2.1 Preparing the database connection and creating the users table
> You can use any relational database: MySQL, PostgreSQL, SQLite.

```php
use PDO;
use SimpleApiBitrix24\ApiDatabaseConfig;
use SimpleApiBitrix24\DatabaseCore\TableManager;

// Create a PDO instance
$pdo = new PDO('mysql:host=localhost;port=3306;dbname=test', 'root', 'password');

// Create a database configuration object.
// If you need custom table or column names, pass them via ApiDatabaseConfig::__construct()
$databaseConfig = ApiDatabaseConfig::build($pdo);

// Create the users table if it does not exist
$tableManager = new TableManager($databaseConfig);
$tableManager->createUsersTableIfNotExists();
```

### 2.2 Creating a REST API client with OAuth 2.0 authorization

```php
use PDO;
use SimpleApiBitrix24\ApiClientBitrix24;
use SimpleApiBitrix24\ApiClientSettings;
use SimpleApiBitrix24\ApiDatabaseConfig;
use SimpleApiBitrix24\DatabaseCore\UserRepository;
use SimpleApiBitrix24\Enums\AuthType;

$pdo = new PDO('mysql:host=localhost;port=3306;dbname=test', 'root', 'password');
$databaseConfig = ApiDatabaseConfig::build($pdo);

// Load a user whose authorization tokens will be used
$repository = new UserRepository($databaseConfig);
$user = $repository->getUserByIdAndMemberId(1, 'bitrix24_member_id');

// Create settings and set default credentials
$apiSettings = new ApiClientSettings(AuthType::TOKEN);
$apiSettings->setDefaultCredentials($user);

// Create the REST API client
$api = new ApiClientBitrix24($apiSettings, $databaseConfig);

print_r($api->call('crm.deals.get', ['ID' => 2]));
```

### 2.3 Application installation and saving user data to the database

```php
use PDO;
use SimpleApiBitrix24\ApiClientBitrix24;
use SimpleApiBitrix24\ApiClientSettings;
use SimpleApiBitrix24\ApiDatabaseConfig;
use SimpleApiBitrix24\DatabaseCore\UserRepository;
use SimpleApiBitrix24\Enums\AuthType;
use SimpleApiBitrix24\Services\Installation\InstallationService;

$pdo = new PDO('mysql:host=localhost;port=3306;dbname=test', 'root', 'password');
$databaseConfig = ApiDatabaseConfig::build($pdo);

$apiSettings = new ApiClientSettings(AuthType::TOKEN);
$api = new ApiClientBitrix24($apiSettings, $databaseConfig);

// Create and save the user in the database
$user = InstallationService::createUserFromProfileAndSave(
    $databaseConfig,
    'local.693c2b5c42e7c3.81926786',
    'fDlCI34BZbbWv31iNm7H1jpwmu5py9vMyMkkVzQ3IC3WQdPQC4',
    $_REQUEST['member_id'],
    $_REQUEST['AUTH_ID'],
    $_REQUEST['REFRESH_ID'],
    $_REQUEST['DOMAIN']
);

// Your installation logic here
$api->setCredentials($user);
print_r($api->call('scope'));

// Finalize installation
InstallationService::finishInstallation();
```

## 3. Detailed usage of the REST API client

### 3.1 Setting default authorization for the REST API client

```php
use SimpleApiBitrix24\ApiClientSettings;

$apiSettings = new ApiClientSettings(AuthType::TOKEN);
$apiSettings->setDefaultCredentials($user);
```

### 3.2 Switching authorization / working with multiple portals or users simultaneously

```php
use PDO;
use SimpleApiBitrix24\ApiClientBitrix24;
use SimpleApiBitrix24\ApiClientSettings;
use SimpleApiBitrix24\ApiDatabaseConfig;
use SimpleApiBitrix24\DatabaseCore\UserRepository;
use SimpleApiBitrix24\Enums\AuthType;
use SimpleApiBitrix24\Services\Installation\InstallationService;

$pdo = new PDO('mysql:host=localhost;port=3306;dbname=test', 'root', 'password');
$databaseConfig = ApiDatabaseConfig::build($pdo);   // If you need custom table or column names, pass them via ApiDatabaseConfig::__construct()
$apiSettings = new ApiClientSettings(AuthType::TOKEN);

// User #1
$repository = new UserRepository($databaseConfig);
$user_1 = $repository->getUserByIdAndMemberId(1, 'bitrix24_member_id_1');

$apiClient_1 = new ApiClientBitrix24($apiSettings, $databaseConfig);
$apiClient_1->setCredentials($user_1);

// User #2
$user_2 = $repository->getFirstAdminByMemberId('bitrix24_member_id_2');

$apiClient_2 = clone $apiClient_1;
$apiClient_2->setCredentials($user_2);

// Now both clients work independently
print_r($apiClient_1->call('profile'));
print_r($apiClient_2->call('profile'));
```

### 3.3 Saving user data to the database

Be careful when passing client_id and client_secret to InstallationService::createUserFromProfileAndSave.
They are stored per user, enabling local apps to behave like multi‑tenant apps.
If you provide incorrect values, token refresh will fail and the client will throw an exception.
```php
use SimpleApiBitrix24\Services\Installation\InstallationService;

InstallationService::createUserFromProfileAndSave(
    $databaseConfig,
    'local.693c2b5c42e7c3.81926786',
    'fDlCI34BZbbWv31iNm7H1jpwmu5py9vMyMkkVzQ3IC3WQdPQC4',
    $_REQUEST['member_id'],
    $_REQUEST['AUTH_ID'],
    $_REQUEST['REFRESH_ID'],
    $_REQUEST['DOMAIN']
);
```

### 3.4 Working with users via UserRepository

```php
use PDO;
use SimpleApiBitrix24\ApiClientBitrix24;
use SimpleApiBitrix24\DatabaseCore\UserRepository;

$pdo = new PDO('mysql:host=localhost;port=3306;dbname=test', 'root', 'password');
$databaseConfig = ApiDatabaseConfig::build($pdo);   // If you need custom table or column names, pass them via ApiDatabaseConfig::__construct()

// All methods and description can be found in the repository implementation
$repository = new UserRepository($databaseConfig);

$user = $repository->getFirstUserByMemberId('member_id');
$user = $repository->getFirstAdminByMemberId('member_id');

$users = $repository->getAllUsersByMemberId('member_id');

$repository->delete($user);
```


## 4. Configuring Bitrix24 REST API rate‑limit handling

> For more details on Bitrix24 REST API limits, see:
>
> https://apidocs.bitrix24.com/limits.html

This client can automatically handle REST API rate‑limit errors.

Examples of handled errors:
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

By default, rate‑limit handling is enabled.

When such an error occurs, the client retries the request after a configured delay, continuing until the script finishes or the application lifetime ends.

```php
use PDO;
use SimpleApiBitrix24\ApiClientBitrix24;
use SimpleApiBitrix24\ApiClientSettings;
use SimpleApiBitrix24\ApiDatabaseConfig;
use SimpleApiBitrix24\Enums\AuthType;

$pdo = new PDO('mysql:host=localhost;port=3306;dbname=test', 'root', 'password');
$databaseConfig = ApiDatabaseConfig::build($pdo);

// by default, limit processing is always enabled when creating an ApiClientSettings object;
// you can disable it or set your own interval for re-request in microseconds
$apiSettings = new ApiClientSettings(AuthType::TOKEN);
$apiSettings
    ->setOperationTimeLimitHandler(true, 5000000)
    ->setQueryLimitExceededHandler(true, 1000000);

$api = new ApiClientBitrix24($apiSettings, $databaseConfig);
```

Additionally, the client handles:

- rare cases of empty responses from Bitrix24 (automatically retries)
- token refresh and saving new tokens to the database

All other errors result in exceptions.


## 5. Logging

At the DEBUG logging level, all requests and responses from the Bitrix24 server will be logged.

At the WARNING logging level, the logs will include:
- when any key inside a Batch request receives an error from the Bitrix24 server
- any error response from the Bitrix24 server
- exceptions thrown by this client

```php
use Monolog\Formatter\LineFormatter;
use Monolog\Handler\RotatingFileHandler;
use Monolog\Logger;
use PDO;
use SimpleApiBitrix24\ApiClientBitrix24;
use SimpleApiBitrix24\ApiClientSettings;
use SimpleApiBitrix24\ApiDatabaseConfig;
use SimpleApiBitrix24\Enums\AuthType;

$pdo = new PDO('mysql:host=localhost;port=3306;dbname=test', 'root', 'password');
$databaseConfig = ApiDatabaseConfig::build($pdo);   // If you need custom table or column names, pass them via ApiDatabaseConfig::__construct()
$apiSettings = new ApiClientSettings(AuthType::TOKEN);

$logger = new Logger('api-b24');
$handler = new RotatingFileHandler(
    storage_path('logs/rest-api-bitrix24.log'),
    15,
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

$api = new ApiClientBitrix24($apiSettings, $databaseConfig, $logger);
```

## 6. Batch

### 6.1 Basic batch request

```php
use SimpleApiBitrix24\ApiClientBitrix24;

//...

$api = new ApiClientBitrix24($apiSettings, $databaseConfig);

$result = $api->callBatch([
    ['method' => 'scope', 'params' => []],
    ['method' => 'crm.deal.list', 'params' => ['select' => ['ID', 'TITLE']]],
]);

print_r($result);
```

### 6.2 Batch service

```php
use SimpleApiBitrix24\ApiClientBitrix24;
use SimpleApiBitrix24\Services\Batch;

// ...

$api = new ApiClientBitrix24($apiSettings, $databaseConfig);

$batchService = new Batch($api);

// Retrieve all items of an entity (works only with list‑type methods)
$getAllResult = $batchService->getAll('tasks.task.list', ['filter' => ['STATUS' => 5]]);

// Returns responses sorted by the same keys you provide
$resultWithKeys = $batchService->callWithKeys([
    'scope_response' => ['method' => 'scope', 'params' => []],
    'deal_list_response' => ['method' => 'crm.deal.list', 'params' => ['select' => ['ID', 'TITLE']]],
])
```
