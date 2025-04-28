<?php

declare(strict_types=1);

namespace Domain\Blueprint\Actions;

use Domain\Blueprint\DataTransferObjects\BlueprintData;
use Domain\Blueprint\DataTransferObjects\SchemaData;
use Domain\Blueprint\Models\Blueprint;

readonly class ImportBlueprintAction
{
    public function __construct(
        private CreateBlueprintAction $createBlueprintAction,
    ) {}

    public function execute(array $row): Blueprint
    {
        $blueprint = Blueprint::whereId($row['id'])->first();

        if ($blueprint) {
            return $blueprint;
        }

        $data = $row;
        $data['schema'] = json_decode((string) $row['schema'], true);

        // unset($row);

        return $this->createBlueprintAction->execute(
            new BlueprintData(
                id: $data['id'] ?? null,
                name: $data['name'],
                schema: SchemaData::fromArray($data['schema'])

            )
        );
    }
}
