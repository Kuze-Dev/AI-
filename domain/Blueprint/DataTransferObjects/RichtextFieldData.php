<?php

declare(strict_types=1);

namespace Domain\Blueprint\DataTransferObjects;

use Domain\Blueprint\Enums\FieldType;
use Domain\Blueprint\Enums\RichtextButton;

class RichtextFieldData extends FieldData
{
    /**
     * @param array<string> $rules
     * @param array<RichtextButton> $buttons
     */
    private function __construct(
        public readonly string $title,
        public readonly FieldType $type = FieldType::RICHTEXT,
        public readonly array $rules = [],
        public readonly array $buttons = [],
    ) {
    }

    public static function fromArray(array $data): self
    {
        if ( ! $data['type'] instanceof FieldType) {
            $data['type'] = FieldType::from($data['type']);
        }

        if ( ! empty($data['buttons'] ?? [])) {
            $data['buttons'] = array_map(
                fn (string|RichtextButton $value) => ! $value instanceof RichtextButton
                    ? RichtextButton::from($value)
                    : $value,
                $data['buttons']
            );
        }

        return new self(...$data);
    }
}
