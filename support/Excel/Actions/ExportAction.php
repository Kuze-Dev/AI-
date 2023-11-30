<?php

declare(strict_types=1);

namespace Support\Excel\Actions;

use Filament\Pages\Actions\Action;

class ExportAction extends Action
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
            ->withActivityLog(
                event: 'exported',
                description: fn (self $action) => 'Exported '.$action->getModelLabel(),
            );
    }
}
