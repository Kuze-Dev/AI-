<?php

declare(strict_types=1);

namespace App\Filament\Resources\TenantResource\Pages;

use App\Features\FeatureContract;
use Illuminate\Support\Str;

trait Support
{
    /**
     * @return array<int, string>
     */
    private static function getNormalizedFeatureNames(array $features): array
    {
        $results = [];

        foreach ($features as $featureClass => $extraOrBool) {
            if (is_bool($extraOrBool)) {
                if ($extraOrBool) {
                    /** @var class-string<FeatureContract> $class */
                    $class = (string) Str::of($featureClass)->replace('_', '\\');
                    $results[] = app($class)->name;
                }

                continue;
            }

            foreach ($extraOrBool as $v) {
                $results[] = $v;
            }
        }

        return $results;
    }
}
