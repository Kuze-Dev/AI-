<?php

declare(strict_types=1);

namespace Support\ConstraintsRelationships;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOneOrMany;
use Illuminate\Database\Eloquent\Relations\Relation;
use ReflectionClass;
use Support\ConstraintsRelationships\Attributes\OnDeleteCascade;
use Support\ConstraintsRelationships\Attributes\OnDeleteRestrict;
use Support\ConstraintsRelationships\Exceptions\DeleteRestrictedException;
use Support\ConstraintsRelationships\Exceptions\InvalidRelationshipConstraintException;

trait ConstraintsRelationships
{
    protected static function bootConstraintsRelationships(): void
    {
        static::deleting(function (self $model) {

            /** @phpstan-ignore function.alreadyNarrowedType (Call to function method_exists() with Domain\Customer\Models\Customer and 'trashed' will always evaluate to true.) */
            if (method_exists($model, 'trashed') && ! $model->trashed()) {
                return;
            }

            foreach ($model->onDeleteRestrictRelations() as $relation) {
                $model->restrictDelete($relation);
            }

            foreach ($model->onDeleteCascadeRelations() as $relation) {
                $model->cascadeDelete($relation);
            }
        });
    }

    protected function onDeleteRestrictRelations(): array
    {
        return $this->getClassAttribute(OnDeleteRestrict::class)->relations ?? [];
    }

    protected function onDeleteCascadeRelations(): array
    {
        return $this->getClassAttribute(OnDeleteCascade::class)->relations ?? [];
    }

    /**
     * @template T of object
     *
     * @param  class-string<T>  $attributeClass
     * @return ?T
     *
     * @phpstan-ignore-next-line Model property accessors should not be used.
     */
    protected function getClassAttribute(string $attributeClass): ?object
    {
        $class = new ReflectionClass($this);

        $attribute = $class->getAttributes($attributeClass)[0] ?? null;

        return $attribute?->newInstance() ?? null;
    }

    protected function restrictDelete(string $relationName): void
    {
        if ($this->{$relationName}()->exists()) {
            throw DeleteRestrictedException::make($this, $relationName);
        }
    }

    protected function cascadeDelete(string $relationName): void
    {

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
