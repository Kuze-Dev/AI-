<?php

declare(strict_types=1);

namespace App\FilamentTenant\Resources;

use App\FilamentTenant\Resources\TenantApiKeyResource\Pages;
use Domain\Tenant\Models\TenantApiKey;
use Domain\Tenant\Support\ApiAbilitties;
use Filament\Forms;
use Filament\Forms\Components\Actions\Action;
use Filament\Forms\Form;
use Filament\Forms\Set;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class TenantApiKeyResource extends Resource
{
    protected static ?string $model = TenantApiKey::class;

    protected static ?string $navigationGroup = 'System';

    protected static ?string $navigationIcon = 'heroicon-o-cloud';

    protected static ?string $navigationLabel = 'API Settings';

    protected static ?string $recordTitleAttribute = 'app_name';

    protected static ?int $navigationSort = 2;

    public static function shouldRegisterNavigation(): bool
    {
        return config('custom.strict_api');
    }

    public static function canAccess(): bool
    {
        return config('custom.strict_api');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make()
                    ->columns(1)
                    ->schema([
                        Forms\Components\Hidden::make('admin_id')
                            ->default(filament_admin()->id),
                        Forms\Components\TextInput::make('app_name')
                            ->label('Application Name')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('api_key')
                            ->label('API Key')
                            ->required()
                            ->readOnly()
                            ->maxLength(255)
                            ->unique(ignoreRecord: true)
                            ->suffixAction(
                                Action::make('generate_api_key')
                                    ->icon('heroicon-m-cog')
                                    ->requiresConfirmation()
                                    ->modalHeading(fn ($state) => $state ? 'Regenerate API Key' : 'Generate API Key'
                                    )
                                    ->modalSubmitActionLabel(fn ($state) => $state ? 'Regenerate' : 'Generate'
                                    )
                                    ->action(function (Set $set, $state) {
                                        $state = Str::random(30); // 60-character secure random string
                                        $set('api_key', $state);
                                        $set('secret_key', Hash::make($state));
                                    })
                            ),
                        Forms\Components\TextInput::make('secret_key')
                            ->label('Secret Key')
                            ->required()
                            ->readOnly()
                            ->maxLength(255)
                            ->unique(ignoreRecord: true),
                        Forms\Components\CheckboxList::make('abilities')
                            ->label('Abilities')
                            ->options(
                                collect(ApiAbilitties::cases())
                                    ->mapWithKeys(fn (ApiAbilitties $fieldType) => [$fieldType->value => Str::headline($fieldType->value)])
                                    ->filter(
                                        fn ($value, $key) => $key !== '*'
                                    )
                                    ->toArray()
                            )
                            ->columns(3)
                            ->default(['read']),

                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('app_name')
                    ->label('Application Name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('api_key')
                    ->label('API Key')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('secret_key')
                    ->label('Secret Key')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('last_used_at')
                    ->label('Last Used At')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTenantApiKeys::route('/'),
            'create' => Pages\CreateTenantApiKey::route('/create'),
            'edit' => Pages\EditTenantApiKey::route('/{record}/edit'),
        ];
    }
}
