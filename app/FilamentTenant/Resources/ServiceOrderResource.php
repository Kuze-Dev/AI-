<?php

declare(strict_types=1);

namespace App\FilamentTenant\Resources;

use App\FilamentTenant\Resources\ServiceOrderResource\Pages\CreateServiceOrder;
use App\FilamentTenant\Resources\ServiceOrderResource\Pages\EditServiceOrder;
use App\FilamentTenant\Resources\ServiceOrderResource\Pages\ListServiceOrder;
use App\FilamentTenant\Resources\ServiceOrderResource\RelationManagers\ServiceBillsRelationManager;
use App\FilamentTenant\Resources\ServiceOrderResource\RelationManagers\ServiceTransactionsRelationManager;
use Domain\ServiceOrder\Models\ServiceOrder;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ServiceOrderResource extends Resource
{
    protected static ?string $model = ServiceOrder::class;

    protected static ?string $navigationIcon = 'heroicon-o-ticket';

    protected static ?string $recordTitleAttribute = 'reference';

    #[\Override]
    public static function getNavigationGroup(): ?string
    {
        return trans('Service Management');
    }

    #[\Override]
    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('reference')
                    ->label(trans('Order ID'))
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('customer_full_name')
                    ->label(trans('Customer'))
                    ->limit(30)
                    ->sortable(['customer_first_name', 'customer_last_name'])
                    ->searchable(['customer_first_name', 'customer_last_name'])
                    ->wrap(),

                Tables\Columns\TextColumn::make('total_price')
                    ->label(trans('Total'))
                    ->money(currency: fn (ServiceOrder $record) => $record->currency_code)
                    ->alignRight()
                    ->sortable(),

                Tables\Columns\TextColumn::make('status')
                    ->translateLabel()
                    ->alignRight()
                    ->alignLeft(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label(trans('Order Date'))
                    ->sortable()
                    ->dateTime(),

                Tables\Columns\TextColumn::make('updated_at')
                    ->translateLabel()
                    ->sortable()
                    ->dateTime()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('updated_at', 'desc');
    }

    #[\Override]
    public static function getRelations(): array
    {
        return [
            ServiceTransactionsRelationManager::class,
            ServiceBillsRelationManager::class,
        ];
    }

    #[\Override]
    public static function getPages(): array
    {
        return [
            'index' => ListServiceOrder::route('/'),
            'create' => CreateServiceOrder::route('/create'),
            'edit' => EditServiceOrder::route('/{record}/edit'),
        ];
    }
}
