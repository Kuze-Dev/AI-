<?php

declare(strict_types=1);

namespace Domain\Blueprint\Models\Casts;

use Domain\Blueprint\DataTransferObjects\SchemaData;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;

/**
 * @implements CastsAttributes<SchemaData, string>
 */
class SchemaDataCast implements CastsAttributes
{
    /**
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @param  string|array  $value
     */
    #[\Override]
    public function get($model, string $key, $value, array $attributes): SchemaData
    {
        if (is_string($value)) {
            $value = json_decode($value, true);
        }

        return SchemaData::fromArray($value);
    }

    /**
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @param  ?array  $value
     */
    #[\Override]
    public function set($model, string $key, $value, array $attributes): string
    {
        if (is_array($value)) {
            $value = SchemaData::fromArray($value);
        }

        return (string) json_encode($value);
    }
}
