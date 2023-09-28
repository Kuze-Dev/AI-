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
use Domain\Service\Enums\BillingCycle;
use Domain\Service\Enums\RecurringPayment;
use Domain\Service\Enums\Status;
use Domain\Service\Models\Service;
use Domain\Taxonomy\Models\TaxonomyTerm;
use Filament\Forms;
use Filament\Resources\Form;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables;
use Filament\Tables\Columns\SpatieMediaLibraryImageColumn;

class ServiceResource extends Resource
{
    use ContextualResource;

    protected static ?string $model = Service::class;

    protected static ?string $navigationIcon = 'heroicon-o-collection';

    protected static ?string $navigationGroup = 'Service Management';

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Form $form): Form
    {
        $categories = TaxonomyTerm::where('taxonomy_id', app(ServiceSettings::class)->service_category)->get();

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
                            Forms\Components\Select::make('blueprint_id')
                                ->label(trans('Blueprint'))
                                ->required()
                                ->preload()
                                ->optionsFromModel(Blueprint::class, 'name')
                                ->disabled(fn (?Service $record) => $record !== null)
                                ->reactive(),
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
                                Forms\Components\TextInput::make('price')
                                    ->translateLabel()
                                    ->mask(fn (Forms\Components\TextInput\Mask $mask) => $mask->money(
                                        prefix: '$',
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
                        // Forms\Components\Section::make('Dynamic Form Builder')
                        //     ->schema([
                        //         SchemaFormBuilder::make('data', fn (?Service $record) => $record?->blueprint->schema)
                        //             ->schemaData(fn (Closure $get) => Blueprint::query()->firstWhere('id', $get('blueprint_id'))?->schema),
                        //     ])
                        //     ->hidden(fn (Closure $get) => $get('blueprint_id') === null)
                        //     ->columnSpan(2),
                    ])->columnSpan(2),
                Forms\Components\Group::make()->schema([
                    Forms\Components\Section::make('Status')
                        ->translateLabel()
                        ->schema([
                            Forms\Components\Toggle::make('status')
                                ->label(
                                    fn ($state) => $state ? ucfirst(trans(Status::ACTIVE->value)) : ucfirst(trans(Status::INACTIVE->value))
                                ),
                            Forms\Components\Toggle::make('pay_upfront'),
                            Forms\Components\Toggle::make('is_subscription')
                                ->label(trans('Subscription-based'))
                                ->reactive()
                                ->helperText('Fields below are only available on subscription'),
                            Forms\Components\Card::make([
                                Forms\Components\Select::make('billing_cycle')
                                    ->options(function () {
                                        $billing = [];
                                        foreach (BillingCycle::cases() as $billingCycle) {
                                            $billing[$billingCycle->name] = $billingCycle->value;
                                        }

                                        return $billing;
                                    })
                                    ->required(fn (Closure $get) => $get('is_subscription') === true),
                                Forms\Components\Select::make('recurring_payment')
                                    ->options(function () {
                                        $payment = [];
                                        foreach (RecurringPayment::cases() as $recurringPayment) {
                                            $payment[$recurringPayment->name] = $recurringPayment->value;
                                        }

                                        return $payment;
                                    })
                                    ->required(fn (Closure $get) => $get('is_subscription') === true),
                            ])
                                ->reactive()
                                ->disabled(fn (Closure $get) => $get('is_subscription') === false),
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

                Tables\Columns\TextColumn::make('price')
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

            ])
            ->actions([
                Tables\Actions\EditAction::make(),
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
            'index' => ListServices::route('/'),
            'create' => CreateService::route('/create'),
            'edit' => EditService::route('/{record}/edit'),
        ];
    }
}
