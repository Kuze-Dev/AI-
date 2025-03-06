<?php

declare(strict_types=1);

namespace Domain\Blueprint\DataTransferObjects;

use Domain\Blueprint\Enums\FieldType;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Str;

class RelatedResourceFieldData extends FieldData
{
    /**
     * @param  array<string>  $rules
     * @param  array<string, mixed>  $relation_scopes
     */
    public function __construct(
        public readonly string $title,
        public readonly string $state_name,
        public readonly string $resource,
        public readonly FieldType $type = FieldType::RELATED_RESOURCE,
        public readonly array $rules = [],
        public readonly bool $translatable = true,
        public readonly bool $multiple = false,
        public readonly array $relation_scopes = [],
        public readonly ?int $min = null,
        public readonly ?int $max = null,
        public readonly ?string $helper_text = null,
    ) {}

    #[\Override]
    public static function fromArray(array $data): self
    {
        if (! $data['type'] instanceof FieldType) {
            $data['type'] = FieldType::from($data['type']);
        }

        return new self(
            title: $data['title'],
            state_name: $data['state_name'] ?? (string) Str::of($data['title'])->lower()->snake(),
            type: $data['type'],
            rules: $data['rules'] ?? [],
            translatable: $data['translatable'] ?? true,
            resource: $data['resource'],
            multiple: $data['multiple'] ?? false,
            relation_scopes: $data['relation_scopes'] ?? [],
            min: $data['min'] ?? null,
            max: $data['max'] ?? null,
            helper_text: $data['helper_text'] ?? null,
        );
    }

    /** @return class-string<Model>|null */
    public function getRelatedModelClass(): ?string
    {
        /** @var class-string<Model>|null */
        return Relation::getMorphedModel($this->resource);
    }

    public function getRelatedModelConfig(): array
    {
        $modelClass = $this->getRelatedModelClass();

        return config("domain.blueprint.related_resources.{$modelClass}", []);
    }

    public function getRelatedModelInstance(): Model
    {
        $modelClass = $this->getRelatedModelClass();

        /** @var Model */
        return new $modelClass;
    }

    /** @return Collection<array-key, Model>|Model|null */
    public function getRelatedResource(mixed $value): Collection|Model|null
    {
        $related = $this->getRelatedModelInstance();

        return $this->multiple
            ? $this->getRelatedResourceQuery()
                ->whereIn($related->getKeyName(), $value)
                ->get()
                ->sortBy(fn (Model $model) => array_search($model->getKey(), $value, true))
            : $this->getRelatedResourceQuery()
                ->where($related->getKeyName(), $value)
                ->first();
    }

    /** @return Builder<Model> */
    public function getRelatedResourceQuery(): Builder
    {
        $model = $this->getRelatedModelInstance();
        $modelQuery = $model->query();

        foreach ($this->relation_scopes as $relationName => $value) {
            /**
             * @var Relation<Model> $relationship
             *
             * @phpstan-ignore generics.lessTypes */
            $relationship = $model->{$relationName}();

            $modelQuery->whereRelation($relationName, $relationship->getRelated()->getKeyName(), $value);
        }

        return $modelQuery;
    }
}
