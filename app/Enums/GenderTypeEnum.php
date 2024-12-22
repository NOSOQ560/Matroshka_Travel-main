<?php

namespace App\Enums;

enum GenderTypeEnum: string
{
    case male = 'male';
    case female = 'female';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
