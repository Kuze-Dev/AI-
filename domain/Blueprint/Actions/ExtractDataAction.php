<?php

declare(strict_types=1);

namespace Domain\Blueprint\Actions;

use Domain\Blueprint\DataTransferObjects\SchemaData;

class ExtractDataAction
{
    public function extractStatePath(array $data, string $parentKey = ''): array
    {
        $statePaths = [];

        foreach ($data as $key => $value) {
            $currentPath = ($parentKey !== '') ? $parentKey . '.' . $key : $key;
            if (is_array($value)) {
                $nestedPaths = $this->extractStatePath($value, $currentPath);
                $statePaths = array_merge($statePaths, $nestedPaths);
            } else {
                $statePaths[] = $currentPath;
            }
        }

        return $statePaths;
    }

    public function extractFieldType(SchemaData $blueprintfieldtype): array
    {
        $fieldTypes = [];

        foreach ($blueprintfieldtype->sections as $section) {
            foreach ($section->fields as $field) {
                if (isset($field->type)) {
                    $fieldTypes[] = $field->type;
                }
            }
        }

        return $fieldTypes;
    }
}
