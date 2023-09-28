<?php

declare(strict_types=1);

namespace App\FilamentTenant\Resources\ProductResource\RelationManagers;

use Domain\Tier\Models\Tier;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Resources\Table;
use Filament\Tables;
use Exception;

class TiersRelationManager extends RelationManager
{
    protected static string $relationship = 'tiers';
    protected static ?string $inverseRelationship = 'products';
    protected static ?string $recordTitleAttribute = 'name';

    /** @throws Exception */
    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name'),
            ])
            ->headerActions([
                Tables\Actions\AssociateAction::make()
                    ->recordSelectSearchColumns(['name']),
            ])
            ->actions([
                Tables\Actions\DissociateAction::make()
                    ->after(function ($record) {

                        /** @var \Domain\Tier\Models\Tier $tier */
                        $tier = Tier::whereName(config('domain.tier.default'))->first();

                        $record->update([
                            'tier_id' => $tier->getKey(),
                        ]);
                    }),
            ]);
    }
}
