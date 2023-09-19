<?php

declare(strict_types=1);

namespace Domain\Blueprint\Actions;

use Domain\Blueprint\Enums\FieldType;

class ExtractDataAction
{
    public function extractStatePathAndFieldTypes($data): array
    {
        $fieldTypes = [];
        foreach ($data as $sections) {
            foreach($sections as $section) {
                $fieldTypes[$section->state_name] = $this->recursivelyExtractFields($section->fields, $section->state_name);
            }
        }

        return $fieldTypes;
    }

    public function recursivelyExtractFields($fields, $currentpath): array
    {
        $fieldTypes = [];
        foreach ($fields as $field) {
            $newPath = $currentpath . '.' . $field->state_name;
            if ($field->type == FieldType::REPEATER) {
                $fieldTypes[$field->state_name] = array_merge($this->recursivelyExtractFields($field->fields, $newPath));
                $fieldTypes[$field->state_name]['type'] = $field->type;
                $fieldTypes[$field->state_name]['statepath'] = $newPath;
            } else {
                $fieldTypes[$field->state_name] = [
                    'type' => $field->type,
                    'statepath' => $newPath,
                ];
            }
        }

        return $fieldTypes;
    }

    public function mergeFields($firstField, $values)
    {
        $mergedFields = [
            'type' => $firstField['type'],
            'statepath' => $firstField['statepath'],
            'value' => $values,
        ];
        if($firstField['type'] == FieldType::REPEATER) {
            foreach($mergedFields['value'] as $mergedFieldkey => $mergedField) {
                foreach($mergedField as $repeaterFieldKey => $repeaterField) {
                    $mergedField[$repeaterFieldKey] = $this->mergeFields(
                        $firstField[$repeaterFieldKey],
                        $mergedField[$repeaterFieldKey]
                    );
                    $mergedFields['value'][$mergedFieldkey] = $mergedField;
                }
            }
        }

        return $mergedFields;
    }

    public function processRepeaterField($field): array
    {
        $data = [];
        if($field['type'] == FieldType::REPEATER) {
            foreach($field['value'] as $value) {
                foreach($value as $repeaterData) {
                    $data[] = $repeaterData;
                }
            }
        } else {
            $data[] = $field;
        }

        return $data;
    }
}
