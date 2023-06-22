<?php

declare(strict_types=1);

namespace App\FilamentTenant\Resources\TierResource\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Resources\Table;
use Filament\Tables;
use Exception;

class CustomersRelationManager extends RelationManager
{
    protected static string $relationship = 'customers';
    protected static ?string $inverseRelationship = 'tier';

    protected static ?string $recordTitleAttribute = 'first_name';

    /** @throws Exception */
    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('full_name'),
            ])
            ->headerActions([
                Tables\Actions\AssociateAction::make(),
            ])
            ->actions([
                Tables\Actions\DissociateAction::make(),
            ]);
    }
}
