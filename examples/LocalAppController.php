<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Monolog\Formatter\LineFormatter;
use Monolog\Handler\RotatingFileHandler;
use Monolog\Logger;
use PDO;
use SimpleApiBitrix24\ApiClientBitrix24;
use SimpleApiBitrix24\ApiClientSettings;
use SimpleApiBitrix24\ApiDatabaseConfig;
use SimpleApiBitrix24\Constants\DatabaseConstants;
use SimpleApiBitrix24\DatabaseCore\TableManager;
use SimpleApiBitrix24\DatabaseCore\UserRepository;
use SimpleApiBitrix24\Enums\AuthType;
use SimpleApiBitrix24\Services\Installation\InstallationService;

/**
 * ПРИМЕР УСТАНОВКИ ЛОКАЛЬНОГО ПРИЛОЖЕНИЯ
 * EXAMPLE OF LOCAL APP INSTALL
 */
class LocalAppController
{
    private ApiClientBitrix24 $api;
    private ApiDatabaseConfig $databaseConfig;

    public function __construct()
    {
        /**
         * STEP 1
         *
         * Создаём объект PDO.
         * We create a PDO object.
         */
        $pdo = new PDO('mysql:host=localhost;port=3306;dbname=test', 'root', 'qweqwe');


        /**
         * STEP 2
         *
         * Создаём объект конфигурации, через который REST API клиент получает информацию о структуре базы данных.
         * We create a configuration object through which the REST API client receives information about the database structure.
         */

        // Вариант 1: вручную задать структуру таблицы  | Option 1: manually describe the table structure
        $this->databaseConfig = new ApiDatabaseConfig(
            pdo: $pdo,
            tableName: DatabaseConstants::TABLE_NAME,
            userIdColumnName: DatabaseConstants::USER_ID_COLUMN_NAME,
            memberIdColumnName: DatabaseConstants::MEMBER_ID_COLUMN_NAME,
            isAdminColumnName: DatabaseConstants::IS_ADMIN_COLUMN_NAME,
            authTokenColumnName: DatabaseConstants::AUTH_TOKEN_COLUMN_NAME,
            refreshTokenColumnName: DatabaseConstants::REFRESH_TOKEN_COLUMN_NAME,
            domainColumnName: DatabaseConstants::DOMAIN_COLUMN_NAME,
            clientIdColumnName: DatabaseConstants::CLIENT_ID_COLUMN_NAME,
            clientSecretColumnName: DatabaseConstants::CLIENT_SECRET_COLUMN_NAME,
            createdAtColumnName: DatabaseConstants::CREATED_AT_COLUMN_NAME,
            updatedAtColumnName: DatabaseConstants::UPDATED_AT_COLUMN_NAME
        );

        // Вариант 2: создать структуру таблицы по умолчанию  | Option 2: create table by default
        $this->databaseConfig = ApiDatabaseConfig::build($pdo);


        /**
         *  STEP 3
         *
         * Создадим объект настроек для REST API клиента.
         * Let’s create a settings object for the REST API client.
         */
        $apiSettings = new ApiClientSettings(AuthType::TOKEN);


        /**
         * STEP 4
         *
         * Создадим объект логгера.
         * Let’s create a logger object.
         */
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


        /**
         * STEP 5
         *
         * Создадим объект REST API клиента.
         * Let’s create a REST API client object.
         */
        $this->api = new ApiClientBitrix24($apiSettings, $this->databaseConfig, $logger);
    }

    public function installApp(): void
    {
        /**
         * STEP 6
         *
         * Перед установкой приложения используем встроенный менеджер для быстрого создания таблицы пользователей.
         * Before installing the application, we use the built‑in quick user table creation manager from this package.
         */
        $tableManager = new TableManager($this->databaseConfig);
        $tableManager->createUsersTableIfNotExists();

        /**
         * STEP 7
         *
         * Сохраняем в базу данных токены и информацию пользователя, запустившего установку приложения.
         * We save to the database the tokens and the information of the user who launched the application installation.
         */
        $user = InstallationService::createUserFromProfileAndSave(
            $this->databaseConfig,
            'local.695714f5c27552.75701047',
            'sKx6LwW4gIXrWulL70Kikba4gJDHADOwaqioBFIVbsqvonG1XQ',
            $_REQUEST['member_id'],
            $_REQUEST['AUTH_ID'],
            $_REQUEST['REFRESH_ID'],
            $_REQUEST['DOMAIN']
        );

        /**
         * STEP 8
         *
         * Здесь ваш код установки приложения, если требуется.
         * Here is your application installation code, if required.
         */
        $this->api->setCredentials($user);
        echo '<pre>';
        print_r($this->api->call('scope'));
        echo PHP_EOL . '<h1>INSTALLATION DONE</h1>';
        echo '</pre>';

        /**
         * STEP 9
         *
         * Вызываем метод завершения установки приложения.
         * Call the method to complete the application installation.
         */
        InstallationService::finishInstallation();
    }


    public function index()
    {
        /**
         * STEP 10
         *
         * Работа с сохранёнными данными пользователей в базе данных
         * Working with saved user data in the database
         *
         * Описание всех методов смотрите в / See the description of all methods in
         * SimpleApiBitrix24\DatabaseCore\UserRepository
         */
        $repository = new UserRepository($this->databaseConfig);

        // Получим пользователя с правами администратора. / Get a user with administrator rights.
        $user = $repository->getFirstAdminByMemberId($_REQUEST['member_id']);

        // или создадим новый объект пользователя и сохраним его сразу в базе из полученных данных от сервера REST API.
        // Or create a new user object and save it directly in the database from the data received from the REST API server.
        $user = InstallationService::createUserFromProfileAndSave(
            $this->databaseConfig,
            'local.695714f5c27552.75701047',
            'sKx6LwW4gIXrWulL70Kikba4gJDHADOwaqioBFIVbsqvonG1XQ',
            $_REQUEST['member_id'],
            $_REQUEST['AUTH_ID'],
            $_REQUEST['REFRESH_ID'],
            $_REQUEST['DOMAIN']
        );

        // Устанавливаем авторизационные данные для REST API клиента. / Set up the authorization for the REST API client.
        $this->api->setCredentials($user);

        echo '<pre>';
        print_r($this->api->call('scope'));
        echo PHP_EOL . '<h1>HELLO BITRIX24 APP</h1>';
        echo '</pre>';
    }
}
