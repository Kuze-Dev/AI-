<?php

declare(strict_types=1);

namespace App\FilamentTenant\Widgets\Report\utils;

class PercentageCalculator
{
    public static function calculatePercentages($products)
    {
        $productCounts = collect($products)->pluck('count')->toArray();
        $total = array_sum($productCounts);
        $percentages = [];

        foreach ($productCounts as $value) {
            $percentage = ($value / $total) * 100;
            $percentages[] = $percentage;
        }

        return $percentages;
    }

    public static function formatProductNamesWithPercentages($products, $percentages)
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
