<?php

declare(strict_types=1);

namespace Support\ConstraintsRelationships\Exceptions;

use Illuminate\Database\Eloquent\Model;
use InvalidArgumentException;

class InvalidRelationshipConstraintException extends InvalidArgumentException
{
    public static function make(Model $model, string $relationName): self
    {
        $modelClass = $model::class;
        $relationClass = $model->{$relationName}()::class;

        return new self("Invalid relationship constraint, `{$modelClass}::{$relationName}()` returns {$relationClass}.");
    }
}
