<?php

declare(strict_types=1);

namespace HalcyonAgile\FilamentExport\Actions;

use Filament\Tables\Actions\BulkAction;
use HalcyonAgile\FilamentExport\Actions\Concerns\ExportsRecords;

class ExportBulkAction extends BulkAction
{
    use ExportsRecords;

    public static function getDefaultName(): ?string
    {
        return 'export';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this
            ->translateLabel()
            ->action(fn (array $data) => $this->processExport($data['writer_type']))
            // ->icon('heroicon-o-download')
            ->form($this->buildExportForm(...))
            ->deselectRecordsAfterCompletion();
    }
}
