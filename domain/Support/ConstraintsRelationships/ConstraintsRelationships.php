<?php

declare(strict_types=1);

namespace Domain\Support\ConstraintsRelationships;

use Domain\Support\ConstraintsRelationships\Attributes\OnDeleteCascade;
use Domain\Support\ConstraintsRelationships\Attributes\OnDeleteRestrict;
use Domain\Support\ConstraintsRelationships\Exceptions\DeleteRestrictedException;
use Domain\Support\ConstraintsRelationships\Exceptions\InvalidRelationshipConstraintException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOneOrMany;
use ReflectionClass;
use Illuminate\Database\Eloquent\Relations\Relation;

trait ConstraintsRelationships
{
    protected static function bootConstraintsRelationships(): void
    {
        static::deleting(function (self $model) {
            if (method_exists($model, 'trashed') && ! $model->trashed()) {
                return;
            }

            $onDeleteRestrict = $model->getClassAttribute(OnDeleteRestrict::class);
            $onDeleteCascade = $model->getClassAttribute(OnDeleteCascade::class);

            foreach ($onDeleteRestrict?->relations ?? [] as $relation) {
                $model->restrictDelete($relation);
            }

            foreach ($onDeleteCascade?->relations ?? [] as $relation) {
                $model->cascadeDelete($relation);
            }
        });
    }

    /**
     * @template T of object
     * @param class-string<T> $attributeClass
     * @return ?T
     */
    protected function getClassAttribute(string $attributeClass): ?object
    {
        $class = new ReflectionClass($this);

        $attribute = $class->getAttributes($attributeClass)[0] ?? null;

        return $attribute?->newInstance() ?? null;
    }

    protected function restrictDelete(string $relationName): void
    {
        if ($this->{$relationName}()->get()->isNotEmpty()) {
            throw DeleteRestrictedException::make($this, $relationName);
        }
    }

    protected function cascadeDelete(string $relationName): void
    {
        /** @var Relation<Model> $relation */
        $relation = $this->{$relationName}();

        if ($relation instanceof BelongsToMany) {
            $relation->sync([]);

            return;
        }

        if ($relation instanceof HasOneOrMany) {
            $relation->get()->each(fn (Model $model) => $model->delete());

            return;
        }

        throw InvalidRelationshipConstraintException::make($this, $relationName);
    }
}
