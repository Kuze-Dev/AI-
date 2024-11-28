<?php

declare(strict_types=1);

namespace HalcyonAgile\FilamentExport\Actions;

use Filament\Actions\Action;
use HalcyonAgile\FilamentExport\Actions\Concerns\ExportsRecords;

class ExportAction extends Action
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
            ->icon('heroicon-o-arrow-down-tray')
            ->form($this->buildExportForm(...));
    }
}
