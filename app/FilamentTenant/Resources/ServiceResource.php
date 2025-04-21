<?php

declare(strict_types=1);

namespace App\FilamentTenant\Resources;

use App\Filament\Resources\ActivityResource\RelationManagers\ActivitiesRelationManager;
use App\FilamentTenant\Resources\ServiceResource\Pages\CreateService;
use App\FilamentTenant\Resources\ServiceResource\Pages\EditService;
use App\FilamentTenant\Resources\ServiceResource\Pages\ListServices;
use App\FilamentTenant\Support\MetaDataFormV2;
use App\FilamentTenant\Support\SchemaFormBuilder;
use App\Settings\ServiceSettings;
use Closure;
use Domain\Blueprint\Models\Blueprint;
use Domain\Currency\Models\Currency;
use Domain\Service\Enums\BillingCycleEnum;
use Domain\Service\Enums\Status;
use Domain\Service\Models\Service;
use Exception;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\SpatieMediaLibraryImageColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Storage;
use League\Flysystem\UnableToCheckFileExistence;

class ServiceResource extends Resource
{
    protected static ?string $model = Service::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $recordTitleAttribute = 'name';

    #[\Override]
    public static function getNavigationGroup(): ?string
    {
        return trans('Service Management');
    }

    #[\Override]
    public static function getGloballySearchableAttributes(): array
    {
        return ['name'];
    }

    #[\Override]
    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Group::make()
                    ->schema([
                        Forms\Components\Section::make([
                            Forms\Components\TextInput::make('name')
                                ->label(trans('Service Name'))
                                ->unique(ignoreRecord: true)
                                ->maxLength(100)
                                ->required(),
                            Forms\Components\RichEditor::make('description')
                                ->translateLabel()
                                ->getUploadedAttachmentUrlUsing(function ($file) {

                                    $storage = Storage::disk(config()->string('filament.default_filesystem_disk'));

                                    try {
                                        if (! $storage->exists($file)) {
                                            return null;
                                        }
                                    } catch (UnableToCheckFileExistence) {
                                        return null;
                                    }

                                    if (config()->string('filament.default_filesystem_disk') === 'r2') {
                                        return $storage->url($file);
                                    } else {
                                        if ($storage->getVisibility($file) === 'private') {
                                            try {
                                                return $storage->temporaryUrl(
                                                    $file,
                                                    now()->addMinutes(5),
                                                );
                                            } catch (\Throwable) {
                                                // This driver does not support creating temporary URLs.
                                            }
                                        }

                                    }
                                }),
                            Forms\Components\Select::make('taxonomyTerms')
                                ->label(trans('Service Category'))

                                // Add fillable property [taxonomyTerms] to allow mass assignment on [Domain\Service\Models\Service].
                                ->multiple()
                                ->maxItems(1)

                                ->relationship(
                                    titleAttribute: 'name',
                                    modifyQueryUsing: fn (Builder $query) => $query
                                        ->where(
                                            'taxonomy_id',
                                            app(ServiceSettings::class)
                                                ->service_category
                                        )
                                )
                                ->searchable()
                                ->preload()
                                ->required(),
                            Forms\Components\SpatieMediaLibraryFileUpload::make('media')
                                ->translateLabel()
                                ->collection('media')
                                ->multiple()
                                ->required()
                                ->preserveFilenames(),
                        ]),
                        Forms\Components\Section::make('Service Price')
                            ->translateLabel()
                            ->schema([
                                Forms\Components\Toggle::make('pay_upfront')
                                    ->reactive()
                                    ->disabled(fn (Get $get) => $get('is_subscription') === true),
                                Forms\Components\TextInput::make('retail_price')
                                    ->translateLabel()
                                    ->prefix(Currency::whereEnabled(true)->firstOrFail()->symbol)
                                    ->rule(
                                        fn () => function (string $attribute, mixed $value, Closure $fail) {
                                            if ($value <= 0) {
                                                $attributeName = ucfirst(explode('.', $attribute)[1]);
                                                $fail("$attributeName must be above zero.");
                                            }
                                        },
                                    )
                                    ->required(),
                                Forms\Components\TextInput::make('selling_price')
                                    ->translateLabel()
                                    ->prefix(Currency::whereEnabled(true)->firstOrFail()->symbol)
                                    ->rule(
                                        fn () => function (string $attribute, mixed $value, Closure $fail) {
                                            if ($value <= 0) {
                                                $attributeName = ucfirst(explode('.', $attribute)[1]);
                                                $fail("$attributeName must be above zero.");
                                            }
                                        },
                                    )
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
                            ->columns(),
                        Forms\Components\Section::make('Dynamic Form Builder')
                            ->schema([
                                Forms\Components\Select::make('blueprint_id')
                                    ->required()
                                    ->relationship('blueprint', 'name')
                                    ->preload()
                                    ->searchable()
                                    ->disabled(fn ($record) => $record !== null)
                                    ->reactive(),
                                SchemaFormBuilder::make('data')
                                    ->schemaData(fn (Get $get) => Blueprint::query()
                                        ->firstWhere('id', $get('blueprint_id'))?->schema)
                                    ->hidden(fn (Get $get) => $get('blueprint_id') === null)
                                    ->dehydrated(false)
                                    ->disabled(),
                            ])
                            ->columnSpan(2),
                    ])->columnSpan(2),
                Forms\Components\Group::make()
                    ->schema([
                        Forms\Components\Section::make('Status')
                            ->translateLabel()
                            ->schema([
                                Forms\Components\Toggle::make('status')
                                    ->label(fn (bool $state) => $state ? Status::ACTIVE->getLabel() : Status::INACTIVE->getLabel())
                                    ->reactive()
                                    ->lazy(),
                                Forms\Components\Toggle::make('is_subscription')
                                    ->afterStateUpdated(fn (Set $set, $state) => $set('pay_upfront', $state))
                                    ->label(trans('Subscription Based'))
                                    ->reactive()
                                    ->helperText(
                                        fn (bool $state) => $state
                                            ? trans("Please provide values on 'billing cycle' and 'due date every' fields")
                                            : null
                                    ),
                                Forms\Components\Select::make('billing_cycle')
                                    ->options(function () {
                                        $billing = [];
                                        foreach (BillingCycleEnum::cases() as $billingCycle) {
                                            $billing[$billingCycle->value] = $billingCycle->name;
                                        }

                                        return $billing;
                                    })
                                    ->reactive()
                                    ->hidden(fn (Get $get) => $get('is_subscription') === false)
                                    ->required(fn (Get $get) => $get('is_subscription') === true),
                                Forms\Components\Select::make('due_date_every')
                                    ->reactive()
                                    ->options(function (Get $get) {
                                        if ($get('billing_cycle') !== BillingCycleEnum::DAILY->value) {
                                            return Arr::except(range(0, 31), 0);
                                        }

                                        return null;
                                    })
                                    ->hidden(
                                        fn (Get $get) => ($get('is_subscription') === false
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
                            ]),
                        MetaDataFormV2::make(),
                    ])->columnSpan(1),
            ])
            ->columns(3);
    }

    /** @throws Exception */
    #[\Override]
    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                SpatieMediaLibraryImageColumn::make('media')
                    ->label('Image')
                    ->collection('media')
                    ->extraImgAttributes(['class' => 'aspect-[5/3] object-fill']),
                Tables\Columns\TextColumn::make('name')
                    ->translateLabel()
                    ->searchable()
                    ->limit(50)
                    ->tooltip(function (Tables\Columns\TextColumn $column): ?string {
                        $state = $column->getState();
                        if (strlen($state) <= $column->getCharacterLimit()) {
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
                Tables\Columns\TextColumn::make('status')
                    ->translateLabel()
                    ->badge()
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

    #[\Override]
    public static function getRelations(): array
    {
        return [
            ActivitiesRelationManager::class,
        ];
    }

    #[\Override]
    public static function getPages(): array
    {
        return [
            'index' => ListServices::route('/'),
            'create' => CreateService::route('/create'),
            'edit' => EditService::route('/{record}/edit'),
        ];
    }

    /** @return Builder<\Domain\Service\Models\Service> */
    #[\Override]
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
