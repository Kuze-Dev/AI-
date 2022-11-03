<?php

declare(strict_types=1);

namespace Domain\Blueprint\DataTransferObjects;

use Carbon\Carbon;
use Domain\Blueprint\Enums\FieldType;
use Illuminate\Support\Arr;

class DatetimeFieldData extends FieldData
{
    /** @param array<string> $rules */
    private function __construct(
        public readonly string $title,
        public readonly FieldType $type = FieldType::DATETIME,
        public readonly array $rules = [],
        public readonly ?Carbon $min = null,
        public readonly ?Carbon $max = null,
        public readonly ?string $format = null,
    ) {
    }

    public static function fromArray(array $data): self
    {
        if ( ! $data['type'] instanceof FieldType) {
            $data['type'] = FieldType::from($data['type']);
        }

        if ( ! $data['min'] instanceof Carbon) {
            $data['min'] = Carbon::parse($data['min']);
        }

        if ( ! $data['max'] instanceof Carbon) {
            $data['max'] = Carbon::parse($data['max']);
        }

        return new self(...Arr::only($data, ['title', 'rules', 'min', 'max', 'format']));
    }
}
