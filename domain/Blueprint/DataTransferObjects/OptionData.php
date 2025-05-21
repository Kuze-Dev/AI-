<?php

declare(strict_types=1);

namespace Domain\Blueprint\DataTransferObjects;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Str;

/**
 * @implements Arrayable<string, mixed>
 */
readonly class OptionData implements Arrayable
{
    private function __construct(
        public string $label,
        public string $value
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            label: $data['label'],
            value: $data['value'] ?? (string) Str::of($data['label'])->lower()->snake(),
        );
    }

    /** @return array<string, mixed> */
    #[\Override]
    public function toArray(): array
    {
        return (array) $this;
    }
}
