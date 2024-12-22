<?php

namespace App\Enums;

enum UserTypeEnum: string
{
    case company = 'company';
    case user = 'user';
    case admin = 'admin';
    case customerServices ='customerServices';
    // case super_admin = 'super_admin';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
