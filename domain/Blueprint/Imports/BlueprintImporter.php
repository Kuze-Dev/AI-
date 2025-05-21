<?php

declare(strict_types=1);

namespace Domain\Blueprint\Imports;

use Domain\Blueprint\Actions\ImportBlueprintAction;
use Domain\Blueprint\Models\Blueprint;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;
use Illuminate\Support\Str;

/**
 * @property-read Blueprint $record
 */
class BlueprintImporter extends Importer
{
    protected static ?string $model = Blueprint::class;

    #[\Override]
    public static function getColumns(): array
    {
        return [

            ImportColumn::make('id')
                ->requiredMapping(),

            ImportColumn::make('name')
                ->rules(['unique:blueprints,name'])
                ->requiredMapping(),

            ImportColumn::make('schema')
                ->requiredMapping(),
        ];
    }

    #[\Override]
    public function resolveRecord(): Blueprint
    {

        if (is_null($this->data['id'])) {
            return new Blueprint;
        }

        return Blueprint::where('id', $this->data['id'])->first() ?? new Blueprint;
    }

    #[\Override]
    public function fillRecord(): void
    {
        /** Disabled Filament Built in Record Creation Handle the Forms
         * Creation thru Domain Level Action
         */
    }

    /**
     * @throws \Throwable
     */
    #[\Override]
    public function saveRecord(): void
    {

        if ($this->record->exists) {
            return;
        }

        app(ImportBlueprintAction::class)
            ->execute($this->data);

    }

    #[\Override]
    public static function getCompletedNotificationBody(Import $import): string
    {
        $body = 'Your Blueprint import has completed and '.
            number_format($import->successful_rows).' '.
            Str::of('row')->plural($import->successful_rows).' imported.';

        if ($failedRowsCount = $import->getFailedRowsCount()) {
            $body .= ' '.number_format($failedRowsCount).' '.Str::of('row')
                ->plural($failedRowsCount).' failed to import.';
        }

        return $body;
    }
}
