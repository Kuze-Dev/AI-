<?php

declare(strict_types=1);

namespace Domain\Blueprint\Actions;

use Domain\Blueprint\Enums\FieldType;

class ExtractDataAction
{
    public function extractStatePathAndFieldTypes(array $data): array
    {
        $fieldTypes = [];
        // dd($data);
        foreach ($data as $section) {
            // dd($section->state_name);
            $fieldTypes[$section->state_name] = $this->recursivelyExtractFields($section->fields, $section->state_name);
        }

        return $fieldTypes;
    }

    public function recursivelyExtractFields(array $fields, string $currentpath): array
    {
        $fieldTypes = [];
        foreach ($fields as $field) {
            $newPath = $currentpath.'.'.$field->state_name;
            if ($field->type == FieldType::REPEATER) {
                $fieldTypes[$field->state_name] = array_merge($this->recursivelyExtractFields($field->fields, $newPath));
                $fieldTypes[$field->state_name]['type'] = $field->type;
                $fieldTypes[$field->state_name]['statepath'] = $newPath;
            } else {
                $fieldTypes[$field->state_name] = [
                    'type' => $field->type,
                    'statepath' => $newPath,
                    'translatable' => $field->translatable ?? true,
                ];
            }
        }

        return $fieldTypes;
    }

    public function mergeFields(array $firstField, array|string|null|bool $values, string $parentStatepath): array
    {
        $mergedFields = [
            'type' => $firstField['type'],
            'statepath' => $parentStatepath,
            'value' => $values,
            'translatable' => $firstField['translatable']
        ];
        $statepath = $mergedFields['statepath'];
        if ($firstField['type'] == FieldType::REPEATER) {
            if (is_array($mergedFields['value'])) {
                foreach ($mergedFields['value'] as $mergedFieldkey => $mergedField) {
                    if (is_array($mergedField)) {
                        foreach ($mergedField as $repeaterFieldKey => $repeaterField) {
                            $mergedField[$repeaterFieldKey] = $this->mergeFields(
                                $firstField[$repeaterFieldKey],
                                $mergedField[$repeaterFieldKey],
                                $statepath.'.'.$mergedFieldkey.'.'.$repeaterFieldKey
                            );

                            $mergedFields['value'][$mergedFieldkey] = $mergedField;
                        }
                    }
                }
            }
        }

        return $mergedFields;
    }

    public function processRepeaterField(array $field): array
    {
        $data = [];
        if ($field['type'] == FieldType::REPEATER && $field['value'] !== null) {

            foreach ($field['value'] as $value) {
                foreach ($value as $repeaterData) {
                    $data[] = $this->processRepeaterField($repeaterData);
                }
            }
        } else {
            $data[] = $field;
        }

        return $data;
    }

    public function flattenArray(array $array): array
    {
        $lastArrays = [];

        foreach ($array as $itemKey => $item) {
            if (is_array($item)) {
                $lastArrays = array_merge($lastArrays, $this->flattenArray($item));
            } else {
                if ($itemKey == 'type') {
                    $lastArrays[] = $array;
                }
            }
        }

        return $lastArrays;
    }
}
