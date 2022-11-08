<?php

declare(strict_types=1);

namespace Domain\Blueprint\Actions;

use Domain\Blueprint\DataTransferObjects\BlueprintData;
use Domain\Blueprint\DataTransferObjects\SchemaData;
use Domain\Blueprint\Exceptions\SchemaModificationException;
use Domain\Blueprint\Models\Blueprint;
use Illuminate\Support\Arr;

class UpdateBlueprintAction
{
    public function execute(Blueprint $blueprint, BlueprintData $blueprintData): Blueprint
    {
        $this->validateChangesOnSchema($blueprint->schema, $blueprintData->schema);

        $blueprint->update([
            'name' => $blueprintData->name,
            'schema' => $blueprintData->schema,
        ]);

        return $blueprint;
    }

    protected function validateChangesOnSchema(SchemaData $oldSchema, SchemaData $newSchema): void
    {
        $newSections = Arr::keyBy($newSchema->sections, 'state_name');

        foreach ($oldSchema->sections as $oldSection) {
            $newSection = $newSections[$oldSection->state_name] ?? null;

            if ($newSection === null) {
                continue;
            }

            $newFields = Arr::keyBy($newSection->fields, 'state_name');

            foreach ($oldSection->fields as $oldField) {
                $newField = $newFields[$oldField->state_name] ?? null;

                if ($newField === null) {
                    continue;
                }

                if ($newField->type !== $oldField->type) {
                    throw SchemaModificationException::fieldTypeModified($newField->state_name);
                }
            }
        }
    }
}
