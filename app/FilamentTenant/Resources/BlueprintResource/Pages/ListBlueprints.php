<?php

declare(strict_types=1);

namespace App\FilamentTenant\Resources\BlueprintResource\Pages;

use App\FilamentTenant\Resources\BlueprintResource;
use Domain\Blueprint\Actions\ImportBlueprintAction;
use Domain\Blueprint\Models\Blueprint;
use Filament\Pages\Actions;
use Filament\Resources\Pages\ListRecords;
use HalcyonAgile\FilamentImport\Actions\ImportAction;

class ListBlueprints extends ListRecords
{
    protected static string $resource = BlueprintResource::class;

    #[\Override]
    protected function getHeaderActions(): array
    {
        return [
            ImportAction::make()
                ->model(Blueprint::class)
                ->uniqueBy('name')
                ->tags([
                    'tenant:'.(tenant('id') ?? 'central'),
                ])
                ->processRowsUsing(
                    fn (array $row): Blueprint => app(ImportBlueprintAction::class)
                        ->execute($row)
                )
                ->withValidation(
                    rules: [
                        'id' => [
                            'required',
                            'distinct',
                        ],
                        'name' => 'required',
                        'schema' => 'required',

                    ],
                ),
            
            Actions\CreateAction::make(),
        ];
    }
}
