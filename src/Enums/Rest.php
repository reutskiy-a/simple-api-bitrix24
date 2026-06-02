<?php

declare(strict_types=1);

namespace SimpleApiBitrix24\Enums;

enum Rest: string
{
    case VER_2 = '2.0';
    case VER_3 = '3.0';

    public function getUrl()
    {
        return match ($this) {
            self::VER_2 => 'url-v2',
            self::VER_3 => 'url-v3',
        };
    }
}
