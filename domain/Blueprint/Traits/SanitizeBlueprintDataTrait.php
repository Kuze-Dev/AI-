<?php

declare(strict_types=1);

namespace Domain\Blueprint\Traits;

trait SanitizeBlueprintDataTrait
{
    /**
     * @param  array|null  $array
     *                             $unsanitize data array from DTO or unfiltered data
     * @param  array  $reference
     *                            $reference blueprint->schema->getFieldStatekeys() / reference or template array
     * @return null|array
     *                    filtered array from $data based on $reference
     */
    public function sanitizeBlueprintData(?array $array, array $reference): ?array
    {
        if (! $array) {
            return null;
        }

        $filteredArray = [];

        foreach ($reference as $key => $value) {
            if (is_array($value)) {
                if (isset($array[$key]) && is_array($array[$key])) {
                    if (isset($array[$key][0]) && is_array($array[$key][0])) {
                        $filteredArray[$key] = array_map(fn($item) => $this->sanitizeBlueprintData($item, $value[0]), $array[$key]);
                    } else {
                        $filteredArray[$key] = $this->sanitizeBlueprintData($array[$key], $value);
                    }
                } else {
                    $filteredArray[$key] = $this->sanitizeBlueprintData(null, $value);
                }
            } else {
                if (array_key_exists($key, $array) && $array[$key] !== null) {
                    $filteredArray[$key] = $array[$key];
                } else {
                    $filteredArray[$key] = null;
                }
            }
        }

        return $filteredArray;

    }
}
