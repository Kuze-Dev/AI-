<?php

declare(strict_types=1);

namespace App\FilamentTenant\Resources\ServiceOrderResource\RelationManagers;

use App\FilamentTenant\Resources\ServiceBillResource;
use Domain\ServiceOrder\Models\ServiceBill;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Resources\Table;
use Filament\Tables;

class ServiceBillRelationManager extends RelationManager
{
    protected static string $relationship = 'serviceBills';
    protected static ?string $title = 'Service Bills';

    public static function table(Table $table): Table
    {
        return ServiceBillResource::table($table)->actions([
            Tables\Actions\Action::make('view')
                ->label(trans('View Details'))
                ->color('secondary')
                ->url(fn (ServiceBill $record) => ServiceBillResource::getUrl('view', [$record])),
        ]);
    }
}
