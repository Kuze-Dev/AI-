<?php

declare(strict_types=1);

namespace App\FilamentTenant\Resources;

use App\Filament\Resources\ActivityResource\RelationManagers\ActivitiesRelationManager;
use Artificertech\FilamentMultiContext\Concerns\ContextualResource;
use Domain\Customer\Actions\DeleteTierAction;
use Domain\Customer\Models\Tier;
use Domain\Support\ConstraintsRelationships\Exceptions\DeleteRestrictedException;
use Filament\Resources\Form;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables;
use Filament\Tables\Filters\Layout;
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
            ])
            ->filters([
                Tables\Filters\TrashedFilter::make(),
            ])
            ->filtersLayout(Layout::AboveContent)
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
                    Tables\Actions\RestoreAction::make(),
                    Tables\Actions\ForceDeleteAction::make(),
                ]),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }

    public static function getRelations(): array
    {
        return [
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
