<?php

declare(strict_types=1);

namespace Support\ConstraintsRelationships\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS)]
class OnDeleteRestrict
{
    public function __construct(
        public readonly array $relations
    ) {}
}
