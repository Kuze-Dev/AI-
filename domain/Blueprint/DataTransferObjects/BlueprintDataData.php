<?php

declare(strict_types=1);

namespace Domain\Blueprint\DataTransferObjects;

use Domain\Blueprint\Enums\FieldType;

class BlueprintDataData
{
    public function __construct(
        public readonly int $blueprint_id,
        public readonly string $state_path,
        public readonly ?array $value,
        public readonly FieldType $type,
    ) {
    }
}
