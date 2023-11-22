<?php

declare(strict_types=1);

namespace Support\Excel\Actions;

use Filament\Tables\Actions\BulkAction;

class ExportBulkAction extends BulkAction
{
    use Concerns\ExportsRecords;

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
            ->icon('heroicon-o-download')
            ->form($this->buildExportForm(...))
            ->deselectRecordsAfterCompletion()
            ->withActivityLog(
                event: 'bulk-exported',
                description: fn (self $action) => 'Bulk Exported '.$action->getModelLabel(),
                properties: fn (self $action) => ['selected_record_ids' => $action->getRecords()?->modelKeys()]
            );
    }
}
