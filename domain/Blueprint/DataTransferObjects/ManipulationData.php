<?php

declare(strict_types=1);

namespace Domain\Blueprint\DataTransferObjects;

use Domain\Blueprint\Enums\ManipulationType;
use Illuminate\Contracts\Support\Arrayable;

/**
 * @implements Arrayable<string, mixed>
 */
readonly class ManipulationData implements Arrayable
{
    public function __construct(
        public ManipulationType $type,
        public array $params = [],
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            type: $data['type'],
            params: $data['params'] ?? [],
        );
    }

    /** @return array<string, mixed> */
    #[\Override]
    public function toArray(): array
    {
        return (array) $this;
    }
}
