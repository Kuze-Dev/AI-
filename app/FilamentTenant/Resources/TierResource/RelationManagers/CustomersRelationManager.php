<?php

declare(strict_types=1);

namespace App\FilamentTenant\Resources\TierResource\RelationManagers;

use Domain\Tier\Models\Tier;
use Exception;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Resources\Table;
use Filament\Tables;

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
                Tables\Actions\AssociateAction::make()
                    ->recordSelectSearchColumns(['first_name', 'last_name']),
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
