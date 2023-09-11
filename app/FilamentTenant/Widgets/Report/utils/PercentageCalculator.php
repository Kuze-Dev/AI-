<?php

declare(strict_types=1);

namespace App\FilamentTenant\Widgets\Report\utils;

class PercentageCalculator
{
    public static function calculatePercentages(array $products): array
    {
        $productCounts = collect($products)->pluck('count')->toArray();
        $total = array_sum($productCounts);
        $percentages = [];

        foreach ($productCounts as $value) {
            $percentage = number_format(($value / $total) * 100, 2);
            $percentages[] = $percentage;
        }

        return $percentages;
    }

    public static function formatProductNamesWithPercentages(array $products, array $percentages): array
    {
        $productNames = collect($products)->pluck('name')->toArray();
        $formattedProductNames = [];

        for ($i = 0; $i < count($productNames); $i++) {
            $formattedName = $productNames[$i] . ' ' . $percentages[$i] . '%';
            $formattedProductNames[] = $formattedName;
        }

        return $formattedProductNames;
    }
}
