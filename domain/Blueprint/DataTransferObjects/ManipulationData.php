<?php

declare(strict_types=1);

namespace Domain\Blueprint\DataTransferObjects;

use Domain\Blueprint\Enums\ManipulationType;
use Illuminate\Contracts\Support\Arrayable;

/**
 * @implements Arrayable<string, mixed>
 */
class ManipulationData implements Arrayable
{
    public function __construct(
        public readonly ManipulationType $type,
        public readonly array $params = [],
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            type: $data['type'],
            params: $data['params'] ?? [],
        );
    }

    /** @return array<string, mixed> */
    #[\Override]
    public function toArray()
    {
        return (array) $this;
    }
}
