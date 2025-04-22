<?php

declare(strict_types=1);

namespace App\FilamentTenant\Widgets\Report\utils;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class DateRangeCalculator
{
    public static function calculateDateRange(?string $activeFilter): array
    {
        $startDate = null;
        $endDate = null;

        if ($activeFilter === 'perDay') {
            $startDate = now()->startOfMonth();
            $endDate = now()->endOfMonth();
        } elseif ($activeFilter === 'perMonth') {
            $startDate = now()->startOfYear();
            $endDate = now()->endOfYear();
        } elseif ($activeFilter === 'perYear') {
            $startDate = now()->startOfDecade();
            $endDate = now()->endOfDecade();
        }

        return [
            'start' => $startDate,
            'end' => $endDate,
        ];
    }

    /** @return Builder<Model> */
    public static function pieDateRange(Builder $query, ?string $activeFilter): Builder // @phpstan-ignore missingType.generics
    {

        if ($activeFilter === 'thisYear') {
            return $query->whereBetween('created_at', [now()->startOfYear(), now()]);
        } elseif ($activeFilter === 'thisMonth') {
            return $query->whereBetween('created_at', [now()->startOfMonth(), now()]);
        } elseif ($activeFilter === 'thisDay') {
            return $query->whereDate('created_at', now()->toDateString());
        }

        return $query;
    }
}
