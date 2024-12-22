<?php

namespace App\Enums;

enum ConfigrationTypeEnum: string
{
    case term = 'term';
    case faq = 'faq';
    case policy = 'policy';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
