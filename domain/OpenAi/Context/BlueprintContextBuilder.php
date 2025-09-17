<?php

declare(strict_types=1);

namespace Domain\OpenAi\Context;

class BlueprintContextBuilder
{
    /**
     * @param  array|object  $blueprint  // sections/fields
     * @param  array  $data  // actual values keyed by state_name
     */
    public static function build(array|object $blueprint, array $data = []): array
    {
        $result = [];

        // Handle top-level: if DTO, try to get sections property
        $sections = [];

        if (is_array($blueprint) && isset($blueprint['sections'])) {
            $sections = $blueprint['sections'];
        } elseif (is_object($blueprint) && property_exists($blueprint, 'sections')) {
            $sections = $blueprint->sections;
        }

        if (! is_iterable($sections)) {
            return $result;
        }

        foreach ($sections as $section) {
            // If SectionData object → read properties directly
            if (is_object($section)) {
                $sectionStateName = $section->state_name ?? null;
                $fieldsData = $section->fields ?? [];
            } else {
                $sectionStateName = $section['state_name'] ?? null;
                $fieldsData = $section['fields'] ?? [];
            }

            // Build values per field
            $sectionValues = [];
            foreach ($fieldsData as $field) {
                $fieldState = is_object($field)
                    ? $field->state_name ?? null
                    : $field['state_name'] ?? null;

                $fieldType = is_object($field)
                    ? $field->type ?? null
                    : $field['type'] ?? null;

                // get actual value from $data
                $value = $data[$fieldState] ?? null;

                // Store both value and type
                $sectionValues[$fieldState] = [
                    'type' => $fieldType,
                ];
            }

            $result[$sectionStateName] = $sectionValues;
        }

        return $result;
    }
}
