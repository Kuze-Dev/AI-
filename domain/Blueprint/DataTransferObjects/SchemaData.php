<?php

declare(strict_types=1);

namespace Domain\Blueprint\DataTransferObjects;

use Illuminate\Contracts\Support\Arrayable;

/**
 * @implements Arrayable<string, mixed>
 */
class SchemaData implements Arrayable
{
    /** @param  array<SectionData>  $sections */
    private function __construct(
        public readonly array $sections
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            sections: array_map(
                fn (array $section) => SectionData::fromArray($section),
                $data['sections']
            )
        );
    }

    /** @return array<string, mixed> */
    #[\Override]
    public function toArray()
    {
        return (array) $this;
    }

    public function getValidationRules(): array
    {
        $rules = [];

        foreach ($this->sections as $section) {
            foreach ($section->fields as $field) {

                if ($field instanceof \Domain\Blueprint\DataTransferObjects\RepeaterFieldData) {

                    $repeaterRule = $field->rules;

                    if (! in_array('array', $repeaterRule)) {
                        $repeaterRule[] = 'array';
                    }

                    $rules[$section->state_name.'.'.$field->state_name] = $repeaterRule;

                    foreach ($field->fields as $repeaterField) {

                        $rules[$section->state_name.'.'.$field->state_name.'.*.'.$repeaterField->state_name] = $repeaterField->rules;
                    }
                } else {

                    $rules[$section->state_name.'.'.$field->state_name] = $field->rules;
                }

            }
        }

        return $rules;
    }

    public function getFieldStatePaths(): array
    {
        $statepaths = [];

        foreach ($this->sections as $section) {
            foreach ($section->fields as $field) {
                $statepaths[] = $section->state_name.'.'.$field->state_name;
            }
        }

        return $statepaths;
    }

    public function getFieldPathLabels(): array
    {
        $statepaths = [];

        foreach ($this->sections as $section) {
            foreach ($section->fields as $field) {
                $statepaths[$section->state_name.'.'.$field->state_name] = $field->title;
            }
        }

        return $statepaths;
    }

    public function getFieldStatekeys(): array
    {
        $statepaths = [];

        foreach ($this->sections as $section) {

            $statepaths[$section->state_name] = $this->processFields($section->fields, []);
        }

        return $statepaths;
    }

    protected function processFields(array $fieldData, array $keys = []): array
    {

        foreach ($fieldData as $field) {

            if ($field instanceof \Domain\Blueprint\DataTransferObjects\RepeaterFieldData) {
                $keys[$field->state_name][] = $this->processFields($field->fields);

            } else {
                $keys[$field->state_name] = $field->type;
            }

        }

        return $keys;

    }
}
