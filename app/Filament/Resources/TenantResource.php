<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Features;
use App\Filament\Resources\ActivityResource\RelationManagers\ActivitiesRelationManager;
use App\Filament\Resources\TenantResource\Pages;
use App\Filament\Rules\CheckDatabaseConnection;
use App\Filament\Rules\FullyQualifiedDomainNameRule;
use App\Filament\Support\Forms\FeatureSelector;
use Domain\Tenant\Models\Tenant;
use Filament\Forms;
use Filament\Resources\Form;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules\Unique;
use Stancl\Tenancy\Database\Models\Domain;

class TenantResource extends Resource
{
    protected static ?string $model = Tenant::class;

    protected static ?string $navigationIcon = 'heroicon-o-office-building';

    protected static ?string $recordTitleAttribute = 'name';

    public static function getGloballySearchableAttributes(): array
    {
        return ['name'];
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Card::make([
                    Forms\Components\TextInput::make('name')
                        ->unique(ignoreRecord: true)
                        ->required(),
                ]),
                Forms\Components\Section::make(trans('Database'))
                    ->statePath('database')
                    ->collapsed(fn (string $context) => $context === 'edit')
                    ->schema([
                        Forms\Components\TextInput::make('host')
                            ->required(fn (?Tenant $record) => $record === null)
                            ->columnSpan(['md' => 3])
                            ->afterStateHydrated(fn (Forms\Components\TextInput $component, ?Tenant $record) => $component->state($record?->getInternal('db_host')))
                            ->rule(
                                new CheckDatabaseConnection(config('tenancy.database.template_tenant_connection'), 'data.database'),
                                fn (string $context) => $context === 'create'
                            ),
                        Forms\Components\TextInput::make('port')
                            ->required(fn (?Tenant $record) => $record === null)
                            ->afterStateHydrated(fn (Forms\Components\TextInput $component, ?Tenant $record) => $component->state($record?->getInternal('db_port'))),
                        Forms\Components\TextInput::make('name')
                            ->required(fn (?Tenant $record) => $record === null)
                            ->columnSpanFull()
                            ->afterStateHydrated(fn (Forms\Components\TextInput $component, ?Tenant $record) => $component->state($record?->getInternal('db_name'))),
                        Forms\Components\TextInput::make('username')
                            ->required(fn (?Tenant $record) => $record === null)
                            ->columnSpan(['md' => 2])
                            ->afterStateHydrated(fn (Forms\Components\TextInput $component, ?Tenant $record) => $component->state($record?->getInternal('db_username'))),
                        Forms\Components\TextInput::make('password')
                            ->password()
                            ->required(fn (?Tenant $record) => $record === null)
                            ->columnSpan(['md' => 2])
                            ->afterStateHydrated(fn (Forms\Components\TextInput $component, ?Tenant $record) => $component->state($record ? 'nice try, but we won\'t show the password' : null)),
                    ])
                    ->columns(['md' => 4])
                    ->disabledOn('edit')
                    ->dehydrated(fn (string $context) => $context !== 'edit'),
                Forms\Components\Section::make(trans('Domains'))
                    ->collapsed(fn (string $context) => $context === 'edit')
                    ->schema([
                        Forms\Components\Repeater::make('domains')
                            ->afterStateHydrated(function (Forms\Components\Repeater $component, ?Tenant $record, ?array $state) {
                                $component->state($record?->domains->toArray() ?? $state);
                            })
                            ->disableItemMovement()
                            ->minItems(1)
                            ->schema([
                                Forms\Components\TextInput::make('domain')
                                    ->required()
                                    ->unique(
                                        'domains',
                                        callback: fn (?Tenant $record, Unique $rule, ?string $state) => $rule
                                            ->when(
                                                $record?->domains->firstWhere('domain', $state),
                                                fn (Unique $rule, ?Domain $domain) => $rule->ignore($domain)
                                            ),
                                    )
                                    ->rules([new FullyQualifiedDomainNameRule()]),
                            ]),
                    ]),
                Forms\Components\Section::make(trans('Features'))
                    ->collapsed(fn (string $context) => $context === 'edit')
                    ->schema([
                        FeatureSelector::make('features')
                            ->options([
                                Features\CMS\CMSBase::class => [
                                    'label' => trans('CMS'),
                                    'extras' => [
                                        Features\CMS\SitesManagement::class => app(Features\CMS\SitesManagement::class)->label,
                                    ],
                                ],
                                Features\ECommerce\ECommerceBase::class => [
                                    'label' => trans('eCommerce'),
                                    'extras' => [],
                                ],
                            ]),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable(),
                Tables\Columns\TagsColumn::make('domains.domain'),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime(timezone: Auth::user()?->timezone)
                    ->sortable(),
            ])
            ->filters([])

            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\DeleteAction::make(),
                ]),
            ])
            ->bulkActions([]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTenants::route('/'),
            'create' => Pages\CreateTenant::route('/create'),
            'edit' => Pages\EditTenant::route('/{record}/edit'),
        ];
    }

    public static function getRelations(): array
    {
        return [
            ActivitiesRelationManager::class,
        ];
    }
}
