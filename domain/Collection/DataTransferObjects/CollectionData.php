<?php

declare (strict_types = 1);

namespace Domain\Collection\DataTransferObjects;

class CollectionData 
{
    public function __construct(
        public readonly string $name,
        public readonly int $blueprint_id,
        public readonly ?string $slug = null,
    ) {
        
    }
}