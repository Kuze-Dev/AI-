<?php

declare(strict_types=1);

namespace Domain\Blueprint\DataTransferObjects;

use Illuminate\Contracts\Support\Arrayable;

/**
 * @implements Arrayable<string, mixed>
 */
readonly class SchemaData implements Arrayable
{
    /** @param  array<SectionData>  $sections */
    private function __construct(
        public array $sections
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
    public function toArray(): array
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

                    if (! in_array('array', $repeaterRule, true)) {
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

    public function getStrictValidationRules(): array
    {
        $rules = [];

        foreach ($this->sections as $section) {

            $rules[$section->state_name] = function ($attribute, $value, $fail) use ($section) {

                $allowedFieldKeys = collect($section->fields)->pluck('state_name')->all();

                $unknownFieldKeys = array_diff(array_keys($value), $allowedFieldKeys);

                if (! empty($unknownFieldKeys)) {
                    $fail("Unrecognized fields in {$attribute}: ".implode(', ', $unknownFieldKeys));
                }
            };

            foreach ($section->fields as $field) {

                if ($field instanceof \Domain\Blueprint\DataTransferObjects\RepeaterFieldData) {
                    // Ensure it's an array
                    $repeaterRule = $field->rules;

                    if (! in_array('array', $repeaterRule, true)) {
                        $repeaterRule[] = 'array';
                    }

                    $repeaterRule[] = function ($attribute, $value, $fail) use ($field) {
                        $allowedKeys = collect($field->fields)->pluck('state_name')->all();

                        foreach ($value as $index => $item) {
                            $unknownKeys = array_diff(array_keys($item), $allowedKeys);

                            if (! empty($unknownKeys)) {
                                $fail("Unrecognized fields in {$attribute}.{$index}: ".implode(', ', $unknownKeys));
                            }
                        }
                    };

                    $rules[$section->state_name.'.'.$field->state_name] = array_merge(['present'], $repeaterRule);

                    // Loop through nested fields inside repeater
                    foreach ($field->fields as $repeaterField) {

                        // Check if this field is also a nested RepeaterFieldData
                        if ($repeaterField instanceof \Domain\Blueprint\DataTransferObjects\RepeaterFieldData) {
                            // Ensure nested repeater is an array
                            $nestedRepeaterRule = $repeaterField->rules;

                            if (! in_array('array', $nestedRepeaterRule, true)) {
                                $nestedRepeaterRule[] = 'array';
                            }

                            $nestedRepeaterRule[] = function ($attribute, $value, $fail) use ($repeaterField) {
                                $allowedNestedKeys = collect($repeaterField->fields)->pluck('state_name')->all();

                                foreach ($value as $index => $item) {
                                    $unknownKeys = array_diff(array_keys($item), $allowedNestedKeys);
                                    if (! empty($unknownKeys)) {
                                        $fail("Unrecognized fields in {$attribute}.{$index}: ".implode(', ', $unknownKeys));
                                    }
                                }
                            };

                            $rules[$section->state_name.'.'.$field->state_name.'.*.'.$repeaterField->state_name] = array_merge(['present'], $nestedRepeaterRule);

                            // Add validation rules for each field inside nested repeater
                            foreach ($repeaterField->fields as $nestedField) {
                                $rules[$section->state_name.'.'.$field->state_name.'.*.'.$repeaterField->state_name.'.*.'.$nestedField->state_name] = array_merge(['present'], $nestedField->rules);
                            }
                        } else {
                            // Regular field inside top-level repeater
                            $rules[$section->state_name.'.'.$field->state_name.'.*.'.$repeaterField->state_name] = array_merge(['present'], $repeaterField->rules);
                        }
                    }
                } else {
                    // Regular non-repeater field
                    $rules[$section->state_name.'.'.$field->state_name] = array_merge(['present'], $field->rules);

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
