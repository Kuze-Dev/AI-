<?php

declare(strict_types=1);

namespace Support\ConstraintsRelationships\Exceptions;

use Illuminate\Database\Eloquent\Model;
use LogicException;

class DeleteRestrictedException extends LogicException
{
    public static function make(Model $model, string $relationName): self
    {
        $modelClass = $model::class;

        return new self("Delete has been restricted as `{$modelClass}::{$relationName}()` has existing entries.");
    }
}
