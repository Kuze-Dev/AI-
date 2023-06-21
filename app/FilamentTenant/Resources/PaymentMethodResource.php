<?php

declare(strict_types=1);

namespace App\FilamentTenant\Resources;

use Artificertech\FilamentMultiContext\Concerns\ContextualResource;
use App\FilamentTenant\Resources\PaymentMethodResource\Pages;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Domain\PaymentMethod\Models\PaymentMethod;
use Filament\Resources\Form;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Closure;
use Filament\Tables;
use Filament\Forms;
use Filament\Tables\Filters\Layout;

class PaymentMethodResource extends Resource
{

    use ContextualResource;

    protected static ?string $model = PaymentMethod::class;

    /** @var string|null */
    protected static ?string $navigationGroup = 'eCommerce';

    protected static ?string $navigationIcon = 'heroicon-o-collection';

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
                Forms\Components\FileUpload::make('logo')
                ->formatStateUsing(function ($record) {
                    return $record?->metaData?->getMedia('logo')
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
                Forms\Components\Select::make('gateway')
                    ->required()
                    ->options([
                        'paypal' => 'PayPaL',
                        'stripe' => 'Stripe',
                        ])
                    ->reactive(),
                Forms\Components\KeyValue::make('credentials')
                ->disableAddingRows()
                ->disableEditingKeys()
                ->disableDeletingRows()
                ->formatStateUsing( function () {

                    return [
                        'paypal_secret_id' => '',
                        'paypal_secret_key' => '',
                    ];
                }),
                
                
                ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                //
            ])
            ->filters([
                //
            ])
            ->filtersLayout(Layout::AboveContent)
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
            //
        ];
    }
    
    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPaymentMethods::route('/'),
            'create' => Pages\CreatePaymentMethod::route('/create'),
            'edit' => Pages\EditPaymentMethod::route('/{record}/edit'),
        ];
    }    
}
