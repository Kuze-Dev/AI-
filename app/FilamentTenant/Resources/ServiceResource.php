<?php

declare(strict_types=1);

namespace App\FilamentTenant\Resources;

use App\FilamentTenant\Resources\ServiceResource\Pages\CreateService;
use App\FilamentTenant\Resources\ServiceResource\Pages\EditService;
use App\FilamentTenant\Resources\ServiceResource\Pages\ListServices;
use App\FilamentTenant\Support\MetaDataForm;
use App\FilamentTenant\Support\SchemaFormBuilder;
use App\Settings\ServiceSettings;
use Artificertech\FilamentMultiContext\Concerns\ContextualResource;
use Closure;
use Domain\Blueprint\Models\Blueprint;
use Domain\Service\Enums\Status;
use Domain\Service\Models\Service;
use Domain\Taxonomy\Models\TaxonomyTerm;
use Filament\Forms;
use Filament\Resources\Form;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables;

class ServiceResource extends Resource
{
    use ContextualResource;

    protected static ?string $model = Service::class;

    protected static ?string $navigationIcon = 'heroicon-o-collection';

    protected static ?string $navigationGroup = 'Service Management';

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
                                ->required()
                                ->when(fn () => ! empty($categories->toArray())),
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
                    ])->columnSpan(2),
                Forms\Components\Group::make()->schema([
                    Forms\Components\Section::make('Status')
                        ->translateLabel()
                        ->schema([
                            Forms\Components\Toggle::make('status')
                                ->label(
                                    fn ($state) => $state ? ucfirst(trans(Status::ACTIVE->value)) : ucfirst(trans(Status::INACTIVE->value))
                                ),
                            Forms\Components\Toggle::make('is_subscription')
                                ->label(trans('Subscription Based')),
                        ]),
                    MetaDataForm::make('Meta Data'),
                ])->columnSpan(1),
                Forms\Components\Section::make('Dynamic Form Builder')
                    ->schema([
                        SchemaFormBuilder::make('data', fn (?Service $record) => $record?->blueprint->schema)
                            ->schemaData(fn (Closure $get) => Blueprint::query()->firstWhere('id', $get('blueprint_id'))?->schema),
                    ])
                    ->hidden(fn (Closure $get) => $get('blueprint_id') === null)
                    ->columnSpan(2),
            ])
            ->columns(3);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([

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
