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
            
            if (is_numeric($key)) {
                $currentPath = $parentKey;
             }else{
                $currentPath = ($parentKey !== '') ? $parentKey . '.' . $key : $key;
             }
            
         
            if (is_array($value)) {
                $nestedPaths = $this->extractStatePath($value, $currentPath);
                $statePaths = array_merge($statePaths, $nestedPaths);
            } else {
                $statePaths[] = $currentPath;
            }
        }

        return $statePaths;
    }

    public function extractFieldType(SchemaData $blueprintfieldtype, array $statePaths): array
    {
        $fieldTypes = [];

        for ($i = 0; $i < count($statePaths); $i++) {
            $statePath = $statePaths[$i];

            foreach ($blueprintfieldtype->sections as $section) {
                foreach ($section->fields as $field) {
                    $currentPath = $section->state_name . '.' . $field->state_name;
                    if ($currentPath === $statePath && isset($field->type)) {
                        $fieldTypes[] = $field->type;
                    }
                }
            }
        }

        return $fieldTypes;
    }
}
