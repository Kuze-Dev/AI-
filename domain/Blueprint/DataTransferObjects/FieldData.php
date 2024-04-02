<?php

declare(strict_types=1);

namespace Domain\Blueprint\DataTransferObjects;

use Domain\Blueprint\Enums\FieldType;
use Illuminate\Contracts\Support\Arrayable;

/**
 * @implements Arrayable<string, mixed>
 */
abstract class FieldData implements Arrayable
{
    /** @param  array<string>  $rules */
    private function __construct(
        public readonly string $title,
        public readonly string $state_name,
        public readonly FieldType $type,
        public readonly array $rules = [],
        public readonly ?string $helper_text = null,
    ) {
    }

    public static function fromArray(array $data): self
    {
        if (! $data['type'] instanceof FieldType) {
            $data['type'] = FieldType::from($data['type']);
        }

        return $data['type']->getFieldDataClass()::fromArray($data);
    }

    /** @return array<string, mixed> */
    #[\Override]
    public function toArray()
    {
        return (array) $this;
    }
}
