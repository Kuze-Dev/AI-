<?php

declare(strict_types=1);

namespace App\FilamentTenant\Resources;

use App\FilamentTenant\Resources\PaymentMethodResource\Pages;
use Domain\PaymentMethod\Actions\DeletePaymentMethodAction;
use Domain\PaymentMethod\Models\PaymentMethod;
use Domain\Payments\Actions\GetAvailablePaymentDriverAction;
use Filament\Forms;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use League\Flysystem\UnableToCheckFileExistence;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Support\ConstraintsRelationships\Exceptions\DeleteRestrictedException;

class PaymentMethodResource extends Resource
{
    protected static ?string $model = PaymentMethod::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $recordTitleAttribute = 'title';

    #[\Override]
    public static function getNavigationGroup(): ?string
    {
        return trans('Shop Configuration');
    }

    #[\Override]
    public static function getGloballySearchableAttributes(): array
    {
        return ['title', 'gateway'];
    }

    #[\Override]
    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make([
                    Forms\Components\TextInput::make('title')
                        ->unique(ignoreRecord: true)
                        ->required(),
                    Forms\Components\TextInput::make('subtitle')
                        ->required(),
                    SpatieMediaLibraryFileUpload::make('logo')
                        ->image()
                        ->beforeStateDehydrated(null)
                        ->dehydrateStateUsing(fn (?array $state) => array_values($state ?? [])[0] ?? null)
                        ->getUploadedFileUsing(static function (Forms\Components\FileUpload $component, string $file): ?array {
                            $mediaClass = config()->string('media-library.media_model', Media::class);

                            /** @var ?Media $media */
                            $media = $mediaClass::findByUuid($file);

                            if (! $media) {
                                return null;
                            }
                            
                            if (config()->string('filament.default_filesystem_disk') === 'r2') {
                                return $media?->getUrl();
                            }

                            if ($component->getVisibility() === 'private') {
                                try {
                                    return $media?->getTemporaryUrl(now()->addMinutes(5));
                                } catch (\Throwable) {
                                    // This driver does not support creating temporary URLs.
                                }
                            }

                            return [
                                'name' => $media->getAttributeValue('name') ?? $media->getAttributeValue('file_name'),
                                'size' => $media->getAttributeValue('size'),
                                'type' => $media->getAttributeValue('mime_type'),
                                'url' => $media->getUrl(),
                            ];
                            // return $media?->getUrl();
                        }),
                    Forms\Components\Toggle::make('status')
                        ->inline(false)
                        ->helperText('If enabled, message here')
                        ->reactive(),
                    Forms\Components\Select::make('gateway')
                        ->required()
                        ->options(fn () => app(GetAvailablePaymentDriverAction::class)->execute())
                        ->reactive(),
                    Forms\Components\Textarea::make('description')
                        ->maxLength(fn (int $value = 250) => $value),

                    Forms\Components\RichEditor::make('instruction')
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

                ]),
            ]);
    }

    /**
     * @throws \Exception
     */
    #[\Override]
    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('gateway')
                    ->badge()
                    ->formatStateUsing(fn ($state) => Str::headline($state))
                    ->sortable()
                    ->searchable(),
                Tables\Columns\IconColumn::make('status')
                    ->label(trans('Enabled'))
                    ->icons([
                        'heroicon-o-check-circle' => fn ($state) => $state == true,
                        'heroicon-o-x-circle' => fn ($state) => $state === false,
                    ])
                    ->color(fn (bool $state) => $state ? 'success' : 'danger'),
                Tables\Columns\TextColumn::make('subtitle')
                    ->sortable()
                    ->searchable(),
            ])
            ->filters([
                Tables\Filters\TrashedFilter::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->hidden(fn (PaymentMethod $record) => $record->trashed()),
                Tables\Actions\RestoreAction::make(),
                // Tables\Actions\ActionGroup::make([
                //     Tables\Actions\DeleteAction::make()
                //         ->using(function (PaymentMethod $record) {
                //             try {
                //                 return app(DeletePaymentMethodAction::class)->execute($record);
                //             } catch (DeleteRestrictedException $e) {
                //                 return false;
                //             }
                //         }),
                // ]),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }

    /** @return Builder<PaymentMethod> */
    #[\Override]
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }

    #[\Override]
    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPaymentMethods::route('/'),
            'create' => Pages\CreatePaymentMethod::route('/create'),
            'edit' => Pages\EditPaymentMethod::route('/{record}/edit'),
        ];
    }
}
