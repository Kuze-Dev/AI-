<?php

declare(strict_types=1);

namespace Domain\Blueprint\Models\Casts;

use Domain\Blueprint\DataTransferObjects\SchemaData;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;

class SchemaDataCast implements CastsAttributes
{
    /**
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @param  mixed  $value
     */
    public function get($model, string $key, $value, array $attributes): SchemaData
    {
        return SchemaData::fromArray($value);
    }

    /**
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @param  mixed  $value
     */
    public function set($model, string $key, $value, array $attributes): string
    {
        if (is_array($value)) {
            $value = SchemaData::fromArray($value);
        }

        return (string) json_encode($value);
    }
}
