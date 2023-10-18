<?php

declare(strict_types=1);

namespace App\FilamentTenant\Resources\ServiceOrderResource\RelationManagers;

use Domain\ServiceOrder\Enums\ServiceTransactionStatus;
use Domain\ServiceOrder\Models\ServiceTransaction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Resources\Table;
use Filament\Tables;

class ServiceTransactionRelationManager extends RelationManager
{
    protected static string $relationship = 'serviceTransactions';
    protected static ?string $title = 'Payment';

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('serviceBill.reference')->exists('serviceBill')
                    ->label('reference')
                    ->sortable(),
                Tables\Columns\TextColumn::make('total_amount')->exists('serviceBill')
                    ->formatStateUsing(function (ServiceTransaction $record) {
                        return $record->currency . ' ' . number_format((float) $record->total_amount, 2, '.', ',');
                    })
                    ->label('Amount')
                    ->sortable(),
                Tables\Columns\TextColumn::make('payment_method.title')->exists('payment_method')
                    ->label('Payment Method')
                    ->sortable(),
                Tables\Columns\BadgeColumn::make('status')
                    ->label(trans('Status'))
                    ->alignRight()
                    ->formatStateUsing(function (string $state): string {
                        return ucfirst($state);
                    })
                    ->color(function ($state) {
                        $newState = str_replace(' ', '_', strtolower($state));

                        return match ($newState) {
                            ServiceTransactionStatus::PAID->value => 'success',
                            ServiceTransactionStatus::PENDING->value => 'warning',
                            ServiceTransactionStatus::REFUNDED->value => 'danger',
                            default => 'secondary',
                        };
                    })->inline()
                    ->alignLeft(),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Updated at')
                    ->sortable(),
            ])
            ->bulkActions([])
            ->defaultSort('updated_at', 'desc');
    }
}
