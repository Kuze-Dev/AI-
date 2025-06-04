<?php

declare(strict_types=1);

namespace Domain\Blueprint\Actions;

use Domain\Blueprint\DataTransferObjects\FieldData;
use Domain\Blueprint\Models\Blueprint;

class GetFieldByStatePathAction
{
    public function execute(Blueprint $blueprint, string $statePath): FieldData
    {

        $flattened = $this->flattenSchemaFields($blueprint->schema);

        throw_if(
            ! array_key_exists($statePath, $flattened),
            \Domain\Blueprint\Exceptions\FieldDataNotFoundException::fieldStateName($statePath)
        );
        /** @var FieldData */
        $field = $flattened[$statePath];

        return $field;

    }

    /**
     * Flattens SchemaData into a dot-notated field map with optional filtering.
     */
    public function flattenSchemaFields(\Domain\Blueprint\DataTransferObjects\SchemaData $schema, ?string $fieldClass = null): array
    {
        $result = [];

        foreach ($schema->sections as $section) {
            $sectionPrefix = $section->state_name;

            foreach ($section->fields as $field) {
                $result += $this->flattenField($field, $sectionPrefix, $fieldClass);
            }
        }

        return $result;
    }

    /**
     * Recursively flattens a single field and its nested children (e.g. in repeaters).
     */
    public function flattenField(object $field, string $prefix, ?string $fieldClass = null): array
    {
        /** @phpstan-ignore-next-line */
        $dotKey = $prefix.'.'.$field->state_name;

        $result = [];

        if (is_null($fieldClass) || $field instanceof $fieldClass) {
            $result[$dotKey] = $field;
        }

        if (property_exists($field, 'fields') && is_array($field->fields)) {
            foreach ($field->fields as $nestedField) {
                $result += $this->flattenField($nestedField, $dotKey, $fieldClass);
            }
        }

        return $result;
    }
}
