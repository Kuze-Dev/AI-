<?php

declare(strict_types=1);

namespace App\FilamentTenant\Resources;

use App\Filament\Resources\ActivityResource\RelationManagers\ActivitiesRelationManager;
use App\FilamentTenant\Resources\TierResource\RelationManagers\CustomersRelationManager;
use Artificertech\FilamentMultiContext\Concerns\ContextualResource;
use Domain\Tier\Actions\DeleteTierAction;
use Domain\Tier\Actions\ForceDeleteTierAction;
use Domain\Tier\Actions\RestoreTierAction;
use Domain\Tier\Models\Tier;
use Exception;
use Filament\Facades\Filament;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Table;
use Filament\Tables;
use Filament\Tables\Actions\DeleteAction;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\DB;
use Support\ConstraintsRelationships\Exceptions\DeleteRestrictedException;

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
                        ->unique(ignoreRecord: true)
                        ->maxLength(255),
                    Forms\Components\RichEditor::make('description')
                        ->translateLabel()
                        ->required()
                        ->string(),
                    Forms\Components\Toggle::make('has_approval'),
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
                        ->before(function (DeleteAction $action, Tier $record) {
                            if ($record->customers()->exists()) {
                                Notification::make()
                                    ->warning()
                                    ->title('Customers exists in this tier!')
                                    ->body('disassociate first before deleting!')
                                    ->persistent()
                                    ->send();

                                $action->cancel();
                            }
                        })
                        ->using(function (Tier $record) {
                            try {
                                return app(DeleteTierAction::class)->execute($record);
                            } catch (DeleteRestrictedException) {
                                return false;
                            }
                        }),
                    Tables\Actions\RestoreAction::make()
                        ->using(
                            fn (Tier $record) => DB::transaction(
                                fn () => app(RestoreTierAction::class)
                                    ->execute($record)
                            )
                        ),
                    Tables\Actions\ForceDeleteAction::make()
                        ->using(function (Tier $record) {
                            try {
                                return app(ForceDeleteTierAction::class)->execute($record);
                            } catch (DeleteRestrictedException) {
                                return false;
                            }
                        }),
                ]),
            ])
            ->bulkActions([
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

    /** @return Builder<\Domain\Tier\Models\Tier> */
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
