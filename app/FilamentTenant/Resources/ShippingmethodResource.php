<?php

declare(strict_types=1);

namespace App\FilamentTenant\Resources;

use App\FilamentTenant\Resources\ShippingmethodResource\Pages;
use Artificertech\FilamentMultiContext\Concerns\ContextualResource;
use Domain\ShippingMethod\Models\ShippingMethod;
use Filament\Forms;
use Filament\Resources\Form;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables;
use Illuminate\Support\Facades\Auth;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Throwable;

class ShippingmethodResource extends Resource
{
    use ContextualResource;

    protected static ?string $model = ShippingMethod::class;

    protected static ?string $navigationGroup = 'Shop Configuration';

    protected static ?string $navigationIcon = 'heroicon-o-truck';

    protected static ?string $recordTitleAttribute = 'title';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Card::make([
                    Forms\Components\TextInput::make('title')
                        ->unique(ignoreRecord: true)
                        ->required(),
                    Forms\Components\TextInput::make('subtitle')
                        ->required(),
                    Forms\Components\RichEditor::make('description'),
                    Forms\Components\FileUpload::make('logo')
                        ->formatStateUsing(function ($record) {
                            return $record?->getMedia('logo')
                                ->mapWithKeys(fn (Media $file) => [$file->uuid => $file->uuid])
                                ->toArray() ?? [];
                        })
                        ->image()
                        ->beforeStateDehydrated(null)
                        ->dehydrateStateUsing(fn (?array $state) => array_values($state ?? [])[0] ?? null)
                        ->getUploadedFileUrlUsing(static function (Forms\Components\FileUpload $component, string $file): ?string {
                            $mediaClass = config('media-library.media_model', Media::class);

                            /** @var ?Media $media */
                            $media = $mediaClass::findByUuid($file);

                            if ($component->getVisibility() === 'private') {
                                try {
                                    return $media?->getTemporaryUrl(now()->addMinutes(5));
                                } catch (Throwable $exception) {
                                    // This driver does not support creating temporary URLs.
                                }
                            }

                            return $media?->getUrl();
                        }),
                    Forms\Components\Toggle::make('status')
                        ->inline(false)
                        ->helperText('If enabled, message here')
                        ->reactive(),
                    Forms\Components\Select::make('driver')
                        ->required()
                        ->options(function () {

                            return [
                                'usps' => 'USPS',
                                'store-pickup' => 'Store Pickup',
                            ];

                        })
                        ->reactive(),
                    Forms\Components\KeyValue::make('ship_from_address')
                        ->label('Ship From Address')
                        ->disableAddingRows()
                        ->disableEditingKeys()
                        ->disableDeletingRows()
                        ->formatStateUsing(function ($state) {
                            if ($state != null) {
                                return $state;
                            }

                            return [
                                'Address' => '',
                                'City' => '',
                                'State' => '',
                                'zip4' => '',
                                'zip5' => '',
                            ];
                        }),

                ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('subtitle')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('driver')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime(timezone: Auth::user()?->timezone)
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

        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListShippingmethods::route('/'),
            'create' => Pages\CreateShippingmethod::route('/create'),
            'edit' => Pages\EditShippingmethod::route('/{record}/edit'),
        ];
    }
}
