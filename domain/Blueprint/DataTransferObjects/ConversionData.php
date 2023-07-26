<?php

declare(strict_types=1);

namespace Domain\Blueprint\DataTransferObjects;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Str;

/**
 * @implements Arrayable<string, mixed>
 */
class ConversionData implements Arrayable
{
    private function __construct(
        public readonly string $name,
        public readonly array $manipulations = [],
    ) {
    }

    public static function fromArray(array $data): self
    {
        if (!empty($data['manipulations'] ?? [])) {
            $data['manipulations'] = array_map(
                fn (array $manipulation) => ManipulationData::fromArray($manipulation),
                $data['manipulations']
            );
        }

        return new self(
            name: $data['name'],
            manipulations: $data['manipulations'] ?? [],
        );
    }

    /** @return array<string, mixed> */
    public function toArray()
    {
        return (array) $this;
    }
}
