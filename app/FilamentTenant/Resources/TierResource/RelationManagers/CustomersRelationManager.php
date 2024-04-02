<?php

declare(strict_types=1);

namespace App\FilamentTenant\Resources\TierResource\RelationManagers;

use Domain\Tier\Models\Tier;
use Exception;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class CustomersRelationManager extends RelationManager
{
    protected static string $relationship = 'customers';

    protected static ?string $inverseRelationship = 'tier';

    protected static ?string $recordTitleAttribute = 'email';

    /** @throws Exception */
    #[\Override]
    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('full_name'),
                Tables\Columns\TextColumn::make('email'),
            ])
            ->headerActions([
                Tables\Actions\AssociateAction::make()
                    ->preloadRecordSelect()
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
