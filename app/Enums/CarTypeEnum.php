<?php

namespace App\Enums;

enum CarTypeEnum: string
{
    case business = 'business';
    case normal = 'normal';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
