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
use Carbon\Carbon;
use Closure;
use Domain\Blueprint\Models\Blueprint;
use Domain\Currency\Models\Currency;
use Domain\Service\Actions\DeleteServiceAction;
use Domain\Service\Enums\BillingCycle;
use Domain\Service\Enums\Status;
use Domain\Service\Models\Service;
use Domain\Taxonomy\Models\TaxonomyTerm;
use Filament\Forms;
use Filament\Resources\Form;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables;
use Filament\Tables\Columns\SpatieMediaLibraryImageColumn;
use Illuminate\Database\Eloquent\Builder;
use Support\ConstraintsRelationships\Exceptions\DeleteRestrictedException;

class ServiceResource extends Resource
{
    use ContextualResource;

    protected static ?string $model = Service::class;

    protected static ?string $navigationIcon = 'heroicon-o-collection';

    protected static ?string $navigationGroup = 'Service Management';

    protected static ?string $recordTitleAttribute = 'name';

    public static function getGloballySearchableAttributes(): array
    {
        return ['name'];
    }

    public static function form(Form $form): Form
    {
        $categories = TaxonomyTerm::whereTaxonomyId(app(ServiceSettings::class)->service_category)->get();
        $defaultCurrency = Currency::whereEnabled(true)->firstOrFail()->symbol;

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
                                ->options(
                                    $categories->sortBy('name')
                                        ->mapWithKeys(fn ($categories) => [$categories->id => $categories->name])
                                        ->toArray()
                                )
                                ->formatStateUsing(
                                    fn (?Service $record) => $record?->taxonomyTerms->first()->id ?? null
                                )
                                ->statePath('taxonomy_term_id')
                                ->required(),
                            Forms\Components\FileUpload::make('images')
                                ->translateLabel()
                                ->mediaLibraryCollection('image')
                                ->image()
                                ->multiple(),
                        ]),
                        Forms\Components\Section::make('Service Price')
                            ->translateLabel()
                            ->schema([
                                Forms\Components\Toggle::make('pay_upfront'),
                                Forms\Components\TextInput::make('retail_price')
                                    ->translateLabel()
                                    ->mask(fn (Forms\Components\TextInput\Mask $mask) => $mask->money(
                                        prefix: $defaultCurrency,
                                        thousandsSeparator: ',',
                                        decimalPlaces: 2,
                                        isSigned: false
                                    ))
                                    ->rules([
                                        function () {
                                            return function (string $attribute, mixed $value, Closure $fail) {
                                                if ($value <= 0) {
                                                    $attributeName = ucfirst(explode('.', $attribute)[1]);
                                                    $fail("{$attributeName} must be above zero.");
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
                                        prefix: $defaultCurrency,
                                        thousandsSeparator: ',',
                                        decimalPlaces: 2,
                                        isSigned: false
                                    ))
                                    ->rules([
                                        function () {
                                            return function (string $attribute, mixed $value, Closure $fail) {
                                                if ($value <= 0) {
                                                    $attributeName = ucfirst(explode('.', $attribute)[1]);
                                                    $fail("{$attributeName} must be above zero.");
                                                }
                                            };
                                        },
                                    ])
                                    ->dehydrateStateUsing(fn ($state) => (float) $state)
                                    ->required(),
                            ]),
                        Forms\Components\Section::make('Section Display')
                            ->translateLabel()
                            ->schema([
                                Forms\Components\Toggle::make('is_special_offer')
                                    ->label(trans('Special Offer')),
                                Forms\Components\Toggle::make('is_featured')
                                    ->label(trans('Featured Service')),
                            ])
                            ->columns(2),
                        Forms\Components\Section::make('Dynamic Form Builder')
                            ->schema([
                                Forms\Components\Select::make('blueprint_id')
                                    ->label(trans('Blueprint'))
                                    ->required()
                                    ->preload()
                                    ->optionsFromModel(Blueprint::class, 'name')
                                    ->disabled(fn (?Service $record) => $record !== null)
                                    ->reactive(),
                                SchemaFormBuilder::make('data', fn (?Service $record) => $record?->blueprint->schema)
                                    ->schemaData(fn (Closure $get) => Blueprint::query()
                                        ->firstWhere('id', $get('blueprint_id'))?->schema)
                                    ->hidden(fn (Closure $get) => $get('blueprint_id') === null)
                                    ->dehydrated(false)
                                    ->disabled(),
                            ])
                            ->columnSpan(2),
                    ])->columnSpan(2),
                Forms\Components\Group::make()->schema([
                    Forms\Components\Section::make('Status')
                        ->translateLabel()
                        ->schema([
                            Forms\Components\Toggle::make('status')
                                ->label(
                                    fn ($state) => $state ? ucfirst(trans(Status::ACTIVE->value))
                                        : ucfirst(trans(Status::INACTIVE->value))
                                ),
                            Forms\Components\Toggle::make('is_subscription')
                                ->label(trans('Subscription-based'))
                                ->reactive()
                                ->helperText('Fields below are only available on subscription'),
                            Forms\Components\Card::make([
                                Forms\Components\Select::make('billing_cycle')
                                    ->options(function () {
                                        $billing = [];
                                        foreach (BillingCycle::cases() as $billingCycle) {
                                            $billing[$billingCycle->value] = $billingCycle->name;
                                        }

                                        return $billing;
                                    })
                                    ->required(fn (Closure $get) => $get('is_subscription') === true),
                                Forms\Components\Select::make('due_date_every')
                                    ->reactive()
                                    ->options(function () {
                                        $days = [];
                                        for ($day = 1; $day <= Carbon::now()->daysInMonth; $day++) {
                                            $days[] = $day;
                                        }

                                        return $days;
                                    })
                                    ->disabled(fn (Closure $get) => $get('billing_cycle') === false)
                                    ->required(fn (Closure $get) => $get('is_subscription') === true),
                            ])
                                ->reactive()
                                ->hidden(fn (Closure $get) => $get('is_subscription') === false),
                        ]),
                    MetaDataForm::make('Meta Data'),
                ])->columnSpan(1),
            ])
            ->columns(3);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                SpatieMediaLibraryImageColumn::make('image')
                    ->collection('image')
                    ->default(
                        fn (Service $record) => $record->getFirstMedia('image') === null
                            ? 'https://via.placeholder.com/500x300/333333/fff?text=No+preview+available'
                            : null
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
                    ->formatStateUsing(fn ($state) => $state
                        ? ucfirst(STATUS::ACTIVE->value)
                        : ucfirst(STATUS::INACTIVE->value))
                    ->color(fn (Service $record) => $record->status ? 'success' : 'secondary')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->translateLabel()
                    ->options(['1' => Status::ACTIVE->value, '0' => Status::INACTIVE->value])
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
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\DeleteAction::make()
                        ->translateLabel()
                        ->using(function (Service $record) {
                            try {
                                return app(DeleteServiceAction::class)->execute($record);
                            } catch (DeleteRestrictedException $e) {
                                return false;
                            }
                        })
                        ->authorize('delete'),
                ]),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make()
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
}
