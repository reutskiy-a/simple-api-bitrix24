<?php

declare(strict_types=1);

namespace SimpleApiBitrix24\Services\Crm;

use SimpleApiBitrix24\ApiClientBitrix24;

class Company
{
    public function __construct(
        private ApiClientBitrix24 $api
    ) {

    }

    /**
     * Добавляет поле-список с часовыми поясами СНГ стран
     *
     * @param string $label
     * @param string $fieldName
     * @param string $helpMessage
     * @return array
     * @throws \Throwable
     */
    public function addCisTimeZonesField(string $label = 'Часовой пояс', string $fieldName = 'TIME_ZONES_CIS',  string $helpMessage = ' '): array
    {
        $fields = [
            'LABEL' => $label,
            'USER_TYPE_ID' => 'enumeration',
            'FIELD_NAME' => $fieldName,
            'MULTIPLE' => 'N',
            'MANDATORY' => 'N',
            'SHOW_FILTER' => 'Y',
            'LIST' => [
                ['VALUE' => '(UTC +02:00) Europe/Chisinau', 'DEF' => 'N', 'XML_ID' => 'Europe/Chisinau', 'SORT' => 100],
                ['VALUE' => '(UTC +02:00) Europe/Kaliningrad', 'XML_ID' => 'Europe/Kaliningrad', 'SORT' => 100],
                ['VALUE' => '(UTC +02:00) Europe/Kyiv', 'XML_ID' => 'Europe/Kyiv', 'SORT' => 100],
                ['VALUE' => '(UTC +03:00) Europe/Kirov', 'XML_ID' => 'Europe/Kirov', 'SORT' => 100],
                ['VALUE' => '(UTC +03:00) Europe/Minsk', 'XML_ID' => 'Europe/Minsk', 'SORT' => 100],
                ['VALUE' => '(UTC +03:00) Europe/Moscow', 'XML_ID' => 'Europe/Moscow', 'SORT' => 100],
                ['VALUE' => '(UTC +03:00) Europe/Simferopol', 'XML_ID' => 'Europe/Simferopol', 'SORT' => 100],
                ['VALUE' => '(UTC +03:00) Europe/Volgograd', 'XML_ID' => 'Europe/Volgograd', 'SORT' => 100],
                ['VALUE' => '(UTC +04:00) Asia/Baku', 'XML_ID' => 'Asia/Baku', 'SORT' => 100],
                ['VALUE' => '(UTC +04:00) Asia/Tbilisi', 'XML_ID' => 'Asia/Tbilisi', 'SORT' => 100],
                ['VALUE' => '(UTC +04:00) Asia/Yerevan', 'XML_ID' => 'Asia/Yerevan', 'SORT' => 100],
                ['VALUE' => '(UTC +04:00) Europe/Astrakhan', 'XML_ID' => 'Europe/Astrakhan', 'SORT' => 100],
                ['VALUE' => '(UTC +04:00) Europe/Samara', 'XML_ID' => 'Europe/Samara', 'SORT' => 100],
                ['VALUE' => '(UTC +04:00) Europe/Saratov', 'XML_ID' => 'Europe/Saratov', 'SORT' => 100],
                ['VALUE' => '(UTC +04:00) Europe/Ulyanovsk', 'XML_ID' => 'Europe/Ulyanovsk', 'SORT' => 100],
                ['VALUE' => '(UTC +05:00) Asia/Aqtau', 'XML_ID' => 'Asia/Aqtau', 'SORT' => 100],
                ['VALUE' => '(UTC +05:00) Asia/Aqtobe', 'XML_ID' => 'Asia/Aqtobe', 'SORT' => 100],
                ['VALUE' => '(UTC +05:00) Asia/Ashgabat', 'XML_ID' => 'Asia/Ashgabat', 'SORT' => 100],
                ['VALUE' => '(UTC +05:00) Asia/Atyrau', 'XML_ID' => 'Asia/Atyrau', 'SORT' => 100],
                ['VALUE' => '(UTC +05:00) Asia/Dushanbe', 'XML_ID' => 'Asia/Dushanbe', 'SORT' => 100],
                ['VALUE' => '(UTC +05:00) Asia/Oral', 'XML_ID' => 'Asia/Oral', 'SORT' => 100],
                ['VALUE' => '(UTC +05:00) Asia/Qostanay', 'XML_ID' => 'Asia/Qostanay', 'SORT' => 100],
                ['VALUE' => '(UTC +05:00) Asia/Qyzylorda', 'XML_ID' => 'Asia/Qyzylorda', 'SORT' => 100],
                ['VALUE' => '(UTC +05:00) Asia/Samarkand', 'XML_ID' => 'Asia/Samarkand', 'SORT' => 100],
                ['VALUE' => '(UTC +05:00) Asia/Tashkent', 'XML_ID' => 'Asia/Tashkent', 'SORT' => 100],
                ['VALUE' => '(UTC +05:00) Asia/Yekaterinburg', 'XML_ID' => 'Asia/Yekaterinburg', 'SORT' => 100],
                ['VALUE' => '(UTC +05:00) Asia/Almaty', 'XML_ID' => 'Asia/Almaty', 'SORT' => 100],
                ['VALUE' => '(UTC +06:00) Asia/Bishkek', 'XML_ID' => 'Asia/Bishkek', 'SORT' => 100],
                ['VALUE' => '(UTC +06:00) Asia/Omsk', 'XML_ID' => 'Asia/Omsk', 'SORT' => 100],
                ['VALUE' => '(UTC +07:00) Asia/Barnaul', 'XML_ID' => 'Asia/Barnaul', 'SORT' => 100],
                ['VALUE' => '(UTC +07:00) Asia/Krasnoyarsk', 'XML_ID' => 'Asia/Krasnoyarsk', 'SORT' => 100],
                ['VALUE' => '(UTC +07:00) Asia/Novokuznetsk', 'XML_ID' => 'Asia/Novokuznetsk', 'SORT' => 100],
                ['VALUE' => '(UTC +07:00) Asia/Novosibirsk', 'XML_ID' => 'Asia/Novosibirsk', 'SORT' => 100],
                ['VALUE' => '(UTC +07:00) Asia/Tomsk', 'XML_ID' => 'Asia/Tomsk', 'SORT' => 100],
                ['VALUE' => '(UTC +08:00) Asia/Irkutsk', 'XML_ID' => 'Asia/Irkutsk', 'SORT' => 100],
                ['VALUE' => '(UTC +09:00) Asia/Chita', 'XML_ID' => 'Asia/Chita', 'SORT' => 100],
                ['VALUE' => '(UTC +09:00) Asia/Khandyga', 'XML_ID' => 'Asia/Khandyga', 'SORT' => 100],
                ['VALUE' => '(UTC +09:00) Asia/Yakutsk', 'XML_ID' => 'Asia/Yakutsk', 'SORT' => 100],
                ['VALUE' => '(UTC +10:00) Asia/Ust-Nera', 'XML_ID' => 'Asia/Ust-Nera', 'SORT' => 100],
                ['VALUE' => '(UTC +10:00) Asia/Vladivostok', 'XML_ID' => 'Asia/Vladivostok', 'SORT' => 100],
                ['VALUE' => '(UTC +11:00) Asia/Magadan', 'XML_ID' => 'Asia/Magadan', 'SORT' => 100],
                ['VALUE' => '(UTC +11:00) Asia/Sakhalin', 'XML_ID' => 'Asia/Sakhalin', 'SORT' => 100],
                ['VALUE' => '(UTC +11:00) Asia/Srednekolymsk', 'XML_ID' => 'Asia/Srednekolymsk', 'SORT' => 100],
                ['VALUE' => '(UTC +12:00) Asia/Anadyr', 'XML_ID' => 'Asia/Anadyr', 'SORT' => 100],
                ['VALUE' => '(UTC +12:00) Asia/Kamchatka', 'XML_ID' => 'Asia/Kamchatka', 'SORT' => 100],
            ],
            'SETTINGS' => [
                'DISPLAY' => 'UI',
                'LIST_HEIGHT' => 1,
            ],
            'SORT' => 2000,
            'HELP_MESSAGE' => $helpMessage
        ];

        return $this->api->rest2()->crmCompanyUserFields()->add($fields);
    }
}
