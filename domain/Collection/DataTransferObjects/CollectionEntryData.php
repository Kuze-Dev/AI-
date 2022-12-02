<?php

declare (strict_types = 1);

namespace Domain\Collection\DataTransferObjects;

class CollectionEntryData
{
    public function __construct(
        public readonly int $collection_id,
        public readonly array $data
    ) {
        
    }
}