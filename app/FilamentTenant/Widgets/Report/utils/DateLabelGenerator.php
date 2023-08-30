<?php

declare(strict_types=1);

namespace App\FilamentTenant\Widgets\Report\utils;

class DateLabelGenerator
{
    public static function generateLabels(string|null $activeFilter): array
    {
        if ($activeFilter === 'perDay') {
            $daysInMonth = (int) now()->endOfYear()->format('t');

            return range(1, $daysInMonth);
        } elseif ($activeFilter === 'perMonth') {
            return ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
        } elseif ($activeFilter === 'perYear') {
            $startOfDecade = intval(date('Y') / 10) * 10;
            $endOfDecade = $startOfDecade + 9;

            return range($startOfDecade, $endOfDecade);
        }

        return [];
    }
}
