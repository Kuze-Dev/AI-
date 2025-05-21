<?php

declare(strict_types=1);

namespace Domain\Blueprint\DataTransferObjects;

readonly class BlueprintData
{
    public function __construct(
        public string $name,
        public SchemaData $schema,
        public ?string $id = null,
    ) {}
}
