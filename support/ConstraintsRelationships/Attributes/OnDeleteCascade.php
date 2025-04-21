<?php

declare(strict_types=1);

namespace Support\ConstraintsRelationships\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS)]
class OnDeleteCascade
{
    public function __construct(
        public readonly array $relations
    ) {}
}
