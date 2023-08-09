<?php

declare(strict_types=1);

namespace App\FilamentTenant\Resources;

use Artificertech\FilamentMultiContext\Concerns\ContextualResource;
use Domain\Report\Actions\UpdateReportEnabledAction;
use Domain\Report\Models\Report;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables;
use Exception;
use App\FilamentTenant\Resources\ReportResource\Pages;

class ReportResource extends Resource
{
    use ContextualResource;
    protected static ?string $navigationGroup = 'eCommerce';

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListReport::route('/'),
        ];
    }

}
