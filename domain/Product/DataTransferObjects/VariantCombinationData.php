<?php

declare(strict_types=1);

namespace Domain\Product\DataTransferObjects;

class VariantCombinationData
{
    public function __construct(
        public readonly string $option,
        public readonly int | string $option_id,
        public readonly string $option_value,
        public readonly int | string $option_value_id,
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            option: $data['option'],
            option_id: $data['option_id'],
            option_value: $data['option_value'],
            option_value_id: $data['option_value_id'],
        );
    }

    public static function withOptionId(int | string $optionId, self $data): self
    {
        return new self(
            option: $data->option,
            option_id: $optionId,
            option_value: $data->option_value,
            option_value_id: $data->option_value_id,
        );
    }

    public static function withOptionValueId(int | string $optionValueId, self $data): self
    {
        return new self(
            option: $data->option,
            option_id: $data->option_id,
            option_value: $data->option_value,
            option_value_id: $optionValueId,
        );
    }
}
