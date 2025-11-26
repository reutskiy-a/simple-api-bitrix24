<?php

declare(strict_types=1);

namespace SimpleApiBitrix24\Services\Installation;

use SimpleApiBitrix24\ApiDatabaseConfig;
use SimpleApiBitrix24\DatabaseCore\Models\User;
use SimpleApiBitrix24\DatabaseCore\UserRepository;

class InstallationService
{
    public static function createUserFromProfileAndSave(
        ApiDatabaseConfig $apiDatabaseConfig,
        string $clientId,
        string $clientSecret,
        string $memberId,
        string $authToken,
        string $refreshToken,
        string $domain
    ): User {
        $userDataFetcher = new Bitrix24UserDataFetcher($domain, $authToken);
        $user = $userDataFetcher->createUserFromProfile(
            memberId: $memberId,
            refreshToken: $refreshToken,
            clientId: $clientId,
            clientSecret: $clientSecret,
        );

        $userRepository = new UserRepository($apiDatabaseConfig);
        $userRepository->save($user);

        return $user;
    }

    public static function finishInstallation(): void
    {
        echo '
        <head>
            <script src="//api.bitrix24.com/api/v1/"></script>
            <script>
                BX24.init(function(){
                    BX24.installFinish();
                });
            </script>
        </head>';
    }
}
