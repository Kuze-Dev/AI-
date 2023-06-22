<?php

declare(strict_types=1);

namespace App\FilamentTenant\Resources;

use App\Filament\Resources\ActivityResource\RelationManagers\ActivitiesRelationManager;
use App\FilamentTenant\Resources\TierResource\RelationManagers\CustomersRelationManager;
use Artificertech\FilamentMultiContext\Concerns\ContextualResource;
use Domain\Customer\Actions\DeleteTierAction;
use Domain\Customer\Actions\ForceDeleteTierAction;
use Domain\Customer\Actions\RestoreTierAction;
use Domain\Customer\Models\Tier;
use Domain\Support\ConstraintsRelationships\Exceptions\DeleteRestrictedException;
use Filament\Facades\Filament;
use Filament\Resources\Form;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Forms;

class TierResource extends Resource
{
    use ContextualResource;

    protected static ?string $model = Tier::class;

    protected static ?string $navigationGroup = 'Customer Management';

    protected static ?string $navigationIcon = 'heroicon-o-academic-cap';

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Card::make([
                    Forms\Components\TextInput::make('name')
                        ->translateLabel()
                        ->required()
                        ->string()
                        ->maxLength(255),
                    Forms\Components\RichEditor::make('description')
                        ->translateLabel()
                        ->required()
                        ->string(),
                ]),
            ]);
    }

    /** @throws Exception */
    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->translateLabel()
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('customers_count')
                    ->translateLabel()
                    ->sortable()
                    ->counts('customers')
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime(timezone: Filament::auth()->user()?->timezone)
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\TrashedFilter::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\DeleteAction::make()
                        ->using(function (Tier $record) {
                            try {
                                return app(DeleteTierAction::class)->execute($record);
                            } catch (DeleteRestrictedException $e) {
                                return false;
                            }
                        }),
                    Tables\Actions\RestoreAction::make()
                        ->using(
                            fn (Tier $record) => app(RestoreTierAction::class)
                                ->execute($record)
                        ),
                    Tables\Actions\ForceDeleteAction::make()
                        ->using(function (Tier $record) {
                            try {
                                return app(ForceDeleteTierAction::class)->execute($record);
                            } catch (DeleteRestrictedException $e) {
                                return false;
                            }
                        }),
                ]),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ])
            ->defaultSort('updated_at', 'desc');
    }

    public static function getRelations(): array
    {
        return [
            CustomersRelationManager::class,
            ActivitiesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => TierResource\Pages\ListTiers::route('/'),
            'create' => TierResource\Pages\CreateTier::route('/create'),
            'edit' => TierResource\Pages\EditTier::route('/{record}/edit'),
        ];
    }

    /** @return Builder<\Domain\Customer\Models\Tier> */
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
