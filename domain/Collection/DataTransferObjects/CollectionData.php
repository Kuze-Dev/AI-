<?php

declare (strict_types = 1);

namespace Domain\Collection\DataTransferObjects;

class CollectionData 
{
    public function __construct(
        public readonly string $name,
        public readonly int $blueprint_id,
        public readonly ?string $slug = null,
        public readonly int $display_publish_dates,
        public readonly ?string $past_publish_date,
        public readonly ?string $future_publsh_date
    ) {
        
    }
}