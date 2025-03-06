<?php

declare(strict_types=1);

namespace Domain\Blueprint\DataTransferObjects;

class BlueprintData
{
    public function __construct(
        public readonly string $name,
        public readonly SchemaData $schema,
    ) {}
}
