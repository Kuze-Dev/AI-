<?php

declare(strict_types=1);

namespace App\HttpTenantApi\Resources\Concerns;

use Domain\Blueprint\DataTransferObjects\FieldData;
use Domain\Blueprint\DataTransferObjects\RelatedResourceFieldData;
use Domain\Blueprint\DataTransferObjects\RepeaterFieldData;
use Domain\Blueprint\DataTransferObjects\SchemaData;
use Domain\Blueprint\DataTransferObjects\SectionData;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use TiMacDonald\JsonApi\JsonApiResource;
use InvalidArgumentException;

trait TransformsSchemaPayload
{
    abstract protected function getSchemaData(): SchemaData;

    protected function transformSchemaPayload(array $data): array
    {
        $transformed = [];

        foreach ($this->getSchemaData()->sections as $section) {
            $transformed[$section->state_name] = $this->transformSection($data[$section->state_name] ?? [], $section);
        }

        return $transformed;
    }

    private function transformSection(array $data, SectionData $sectionData): array
    {
        $transformed = [];

        foreach ($sectionData->fields as $field) {
            $transformed[$field->state_name] = $this->transformField($data[$field->state_name] ?? null, $field);
        }

        return $transformed;
    }

    private function transformField(mixed $value, FieldData $field): mixed
    {
        if ($field instanceof RepeaterFieldData) {
            $transformed = [];

            foreach ($field->fields as $field) {
                $transformed[$field->state_name] = $this->transformField($value[$field->state_name], $field);
            }

            return $transformed;
        }

        if ($field instanceof RelatedResourceFieldData) {
            $related = $field->getRelatedResource($value);

            $resourceClass = $this->guessModelResource($field->getRelatedModelInstance());

            return $related instanceof Collection
                ? $resourceClass::collection($related)
                : $resourceClass::make($related);
        }

        return $value;
    }

    /** @return class-string<JsonApiResource> */
    private function guessModelResource(Model $model): string
    {
        $modelBasename = class_basename($model::class);

        /** @var class-string<JsonApiResource> */
        $resourceClass = "App\HttpTenantApi\Resources\\{$modelBasename}Resource";

        if (class_exists($resourceClass) && is_subclass_of($resourceClass, JsonApiResource::class)) {
            return $resourceClass;
        }

        throw new InvalidArgumentException('Can not guess the `JsonApiResource` for `' . $model::class . '`');
    }
}
