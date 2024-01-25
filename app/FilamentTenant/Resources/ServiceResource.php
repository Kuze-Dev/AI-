<?php

declare(strict_types=1);

namespace App\FilamentTenant\Resources;

use App\Filament\Resources\ActivityResource\RelationManagers\ActivitiesRelationManager;
use App\FilamentTenant\Resources\ServiceResource\Pages\CreateService;
use App\FilamentTenant\Resources\ServiceResource\Pages\EditService;
use App\FilamentTenant\Resources\ServiceResource\Pages\ListServices;
use App\FilamentTenant\Support\MetaDataForm;
use App\FilamentTenant\Support\SchemaFormBuilder;
use App\Settings\ServiceSettings;
use Artificertech\FilamentMultiContext\Concerns\ContextualResource;
use Closure;
use Domain\Blueprint\Models\Blueprint;
use Domain\Currency\Models\Currency;
use Domain\Service\Actions\DeleteServiceAction;
use Domain\Service\Enums\BillingCycleEnum;
use Domain\Service\Enums\Status;
use Domain\Service\Models\Service;
use Domain\Taxonomy\Models\TaxonomyTerm;
use Exception;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Table;
use Filament\Tables;
use Filament\Tables\Columns\SpatieMediaLibraryImageColumn;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Arr;
use Support\ConstraintsRelationships\Exceptions\DeleteRestrictedException;

class ServiceResource extends Resource
{

    protected static ?string $model = Service::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationGroup = 'Service Management';

    protected static ?string $recordTitleAttribute = 'name';

    public static function getGloballySearchableAttributes(): array
    {
        return ['name'];
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Group::make()
                    ->schema([
                        Forms\Components\Card::make([
                            Forms\Components\TextInput::make('name')
                                ->label(trans('Service Name'))
                                ->unique(ignoreRecord: true)
                                ->maxLength(100)
                                ->required(),
                            Forms\Components\RichEditor::make('description')
                                ->translateLabel()
                                ->maxLength(255),
                            Forms\Components\Select::make('taxonomyTerms')
                                ->label(trans('Service Category'))
                                ->options(function () {
                                    $categories = TaxonomyTerm::whereTaxonomyId(app(ServiceSettings::class)
                                        ->service_category)->get();

                                    return $categories->sortBy('order')
                                        ->mapWithKeys(fn ($categories) => [$categories->id => $categories->name])
                                        ->toArray();
                                })
                                ->statePath('taxonomy_term_id')
                                ->formatStateUsing(function ($record, $state) {
                                    $oldRecord = $record?->taxonomyTerms->first()->id ?? null;
                                    $categories = TaxonomyTerm::whereTaxonomyId(app(ServiceSettings::class)
                                        ->service_category)->get()->sortBy('order')
                                        ->mapWithKeys(fn ($categories) => [$categories->id => $categories->name])
                                        ->toArray();

                                    return Arr::exists($categories, $oldRecord) ? $oldRecord : $state;
                                })
                                ->required(),
                            Forms\Components\FileUpload::make('media')
                                ->statePath('media')
                                ->translateLabel()
                                ->mediaLibraryCollection('media')
                                ->acceptedFileTypes([
                                    'video/*',
                                    'image/*',
                                ])
                                ->multiple()
                                ->required(),
                        ]),
                        self::servicePriceSection(),
                        Forms\Components\Section::make('Section Display')
                            ->translateLabel()
                            ->schema([
                                Forms\Components\Toggle::make('is_special_offer')
                                    ->label(trans('Special Offer')),
                                Forms\Components\Toggle::make('is_featured')
                                    ->label(trans('Featured Service')),
                            ])
                            ->columns(),
                        Forms\Components\Section::make('Dynamic Form Builder')
                            ->schema([
                                Forms\Components\Select::make('blueprint_id')
                                    ->label(trans('Blueprint'))
                                    ->required()
                                    ->preload()
                                    ->optionsFromModel(Blueprint::class, 'name')
                                    ->disabled(fn ($record) => $record !== null)
                                    ->reactive(),
                                SchemaFormBuilder::make('data')
                                    ->schemaData(fn (\Filament\Forms\Get $get) => Blueprint::query()
                                        ->firstWhere('id', $get('blueprint_id'))?->schema)
                                    ->hidden(fn (\Filament\Forms\Get $get) => $get('blueprint_id') === null)
                                    ->dehydrated(false)
                                    ->disabled(),
                            ])
                            ->columnSpan(2),
                    ])->columnSpan(2),
                Forms\Components\Group::make()
                    ->schema([
                        self::statusSection(),
                        MetaDataForm::make('Meta Data'),
                    ])->columnSpan(1),
            ])
            ->columns(3);
    }

    /** @throws Exception */
    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                SpatieMediaLibraryImageColumn::make('media')
                    ->label('Image')
                    ->collection('media')
                    ->default(
                        fn ($record) => $record->getFirstMedia('media') ?? 'https://via.placeholder.com/500x300/333333/fff?text=No+preview+available'
                    )
                    ->extraImgAttributes(['class' => 'aspect-[5/3] object-fill']),
                Tables\Columns\TextColumn::make('name')
                    ->translateLabel()
                    ->searchable()
                    ->limit(50)
                    ->tooltip(function (Tables\Columns\TextColumn $column): ?string {
                        $state = $column->getState();
                        if (strlen($state) <= $column->getLimit()) {
                            return null;
                        }

                        return $state;
                    })
                    ->sortable(),

                Tables\Columns\TextColumn::make('selling_price')
                    ->translateLabel()
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('retail_price')
                    ->translateLabel()
                    ->searchable()
                    ->sortable(),
                Tables\Columns\BadgeColumn::make('status')
                    ->translateLabel()
                    ->formatStateUsing(fn ($state) => $state ? ucfirst(STATUS::ACTIVE->value) : ucfirst(STATUS::INACTIVE->value))
                    ->color(fn ($record) => $record->status ? 'success' : 'secondary')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->translateLabel()
                    ->options(['1' => ucfirst(Status::ACTIVE->value), '0' => ucfirst(Status::INACTIVE->value)])
                    ->query(function (Builder $query, array $data) {
                        $query->when(filled($data['value']), function (Builder $query) use ($data) {
                            $query->when(filled($data['value']), function (Builder $query) use ($data) {
                                /** @var Service|Builder $query */
                                match ($data['value']) {
                                    '1' => $query->whereStatus(true),
                                    '0' => $query->whereStatus(false),
                                    default => '',
                                };
                            });
                        });
                    }),
                Tables\Filters\TrashedFilter::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->translateLabel()
                    ->authorize('update'),
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\DeleteAction::make()
                        ->translateLabel()
                        ->using(function ($record) {
                            try {
                                return app(DeleteServiceAction::class)->execute($record);
                            } catch (DeleteRestrictedException) {
                                return false;
                            }
                        })
                        ->authorize('delete'),
                    Tables\Actions\RestoreAction::make()
                        ->translateLabel()
                        ->authorize('restore'),
                ]),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make()
                    ->translateLabel(),
                Tables\Actions\RestoreBulkAction::make()
                    ->translateLabel(),
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
            'index' => ListServices::route('/'),
            'create' => CreateService::route('/create'),
            'edit' => EditService::route('/{record}/edit'),
        ];
    }

    /** @return Builder<\Domain\Service\Models\Service> */
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }

    public static function servicePriceSection(): Forms\Components\Section
    {
        return Forms\Components\Section::make('Service Price')
            ->translateLabel()
            ->schema([
                Forms\Components\Toggle::make('pay_upfront')
                    ->reactive()
                    ->disabled(fn (\Filament\Forms\Get $get) => $get('is_subscription') === true),
                Forms\Components\TextInput::make('retail_price')
                    ->translateLabel()
                    ->mask(fn (Forms\Components\TextInput\Mask $mask) => $mask->money(
                        prefix: Currency::whereEnabled(true)->firstOrFail()->symbol,
                        isSigned: false
                    ))
                    ->rules([
                        function () {
                            return function (string $attribute, mixed $value, Closure $fail) {
                                if ($value <= 0) {
                                    $attributeName = ucfirst(explode('.', $attribute)[1]);
                                    $fail("$attributeName must be above zero.");
                                }
                            };
                        },
                    ])
                    ->dehydrateStateUsing(fn ($state) => (float) $state)
                    ->required(),
                Forms\Components\TextInput::make('selling_price')
                    ->translateLabel()
                    // Put custom rule to validate minimum value
                    ->mask(fn (Forms\Components\TextInput\Mask $mask) => $mask->money(
                        prefix: Currency::whereEnabled(true)->firstOrFail()->symbol,
                        isSigned: false
                    ))
                    ->rules([
                        function () {
                            return function (string $attribute, mixed $value, Closure $fail) {
                                if ($value <= 0) {
                                    $attributeName = ucfirst(explode('.', $attribute)[1]);
                                    $fail("$attributeName must be above zero.");
                                }
                            };
                        },
                    ])
                    ->dehydrateStateUsing(fn ($state) => (float) $state)
                    ->required(),
            ]);
    }

    public static function statusSection(): Forms\Components\Section
    {
        return Forms\Components\Section::make('Status')
            ->translateLabel()
            ->schema([
                Forms\Components\Toggle::make('status')
                    ->label(fn ($state) => $state ? ucfirst(trans(Status::ACTIVE->value)) : ucfirst(trans(Status::INACTIVE->value)))
                    ->reactive()
                    ->lazy()
                    ->afterStateUpdated(
                        fn (Forms\Components\Toggle $component) => $component->dispatchEvent('status::update')
                    ),
                Forms\Components\Toggle::make('is_subscription')
                    ->afterStateUpdated(fn (\Filament\Forms\Set $set, $state) => $set('pay_upfront', $state))
                    ->label(trans('Subscription Based'))
                    ->reactive()
                    ->helperText(fn (\Filament\Forms\Get $get) => $get('is_subscription') === true
                        ? "Please provide values on 'billing cycle' and 'due date every' fields" : ''),
                Forms\Components\Select::make('billing_cycle')
                    ->options(function () {
                        $billing = [];
                        foreach (BillingCycleEnum::cases() as $billingCycle) {
                            $billing[$billingCycle->value] = $billingCycle->name;
                        }

                        return $billing;
                    })
                    ->reactive()
                    ->hidden(fn (\Filament\Forms\Get $get) => $get('is_subscription') === false)
                    ->required(fn (\Filament\Forms\Get $get) => $get('is_subscription') === true),
                Forms\Components\Select::make('due_date_every')
                    ->reactive()
                    ->options(function (\Filament\Forms\Get $get) {
                        if ($get('billing_cycle') !== BillingCycleEnum::DAILY->value) {
                            return Arr::except(range(0, 31), 0);
                        }

                        return null;
                    })
                    ->hidden(
                        fn (\Filament\Forms\Get $get) => ($get('is_subscription') === false
                        || $get('billing_cycle') === BillingCycleEnum::DAILY->value)
                    )
                    ->required(),
                Forms\Components\Toggle::make('is_auto_generated_bill')
                    ->label(trans('Auto Generate Bill'))
                    ->reactive(),
                Forms\Components\Toggle::make('needs_approval')
                    ->label(trans('Needs Approval')),
                Forms\Components\Toggle::make('is_partial_payment')
                    ->label(trans('Partial Payment')),
                //                Forms\Components\Toggle::make('is_installment')
                //                    ->label(trans('Installment')),
            ])
            ->registerListeners([
                'status::update' => [
                    function (Forms\Components\Section $component): void {
                        $component->evaluate(function (\Filament\Forms\Get $get, \Filament\Forms\Set $set) {
                            if ($get('status')) {
                                $set('status')
                                    ->label(fn ($state) => $state ? ucfirst(trans(Status::ACTIVE->value)) : ucfirst(trans(Status::INACTIVE->value)));
                            }
                        });
                    },
                ],
            ]);
    }
}
