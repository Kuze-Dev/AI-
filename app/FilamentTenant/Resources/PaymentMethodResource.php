<?php

declare(strict_types=1);

namespace App\FilamentTenant\Resources;

use App\FilamentTenant\Resources\PaymentMethodResource\Pages;
use Artificertech\FilamentMultiContext\Concerns\ContextualResource;
use Domain\PaymentMethod\Actions\DeletePaymentMethodAction;
use Domain\PaymentMethod\Models\PaymentMethod;
use Domain\Payments\Actions\GetAvailablePaymentDriverAction;
use Filament\Forms;
use Filament\Resources\Form;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use League\Flysystem\UnableToCheckFileExistence;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Support\ConstraintsRelationships\Exceptions\DeleteRestrictedException;
use Throwable;

class PaymentMethodResource extends Resource
{
    use ContextualResource;

    protected static ?string $model = PaymentMethod::class;

    protected static ?string $navigationIcon = 'heroicon-o-collection';

    protected static ?string $recordTitleAttribute = 'title';

    public static function getNavigationGroup(): ?string
    {
        return trans('Shop Configuration');
    }

    public static function getGloballySearchableAttributes(): array
    {
        return ['title', 'gateway'];
    }

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

                            if (config('filament.default_filesystem_disk') === 'r2') {
                                return $media?->getUrl();
                            }

                            if ($component->getVisibility() === 'private') {
                                try {
                                    return $media?->getTemporaryUrl(now()->addMinutes(5));
                                } catch (Throwable) {
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
                        ->options(function () {

                            return app(GetAvailablePaymentDriverAction::class)->execute();

                        })
                        ->reactive(),
                    Forms\Components\Textarea::make('description')
                        ->maxLength(fn (int $value = 250) => $value),

                    Forms\Components\RichEditor::make('instruction')
                        ->getUploadedAttachmentUrlUsing(function ($file) {

                            $storage = Storage::disk(config('filament.default_filesystem_disk'));

                            try {
                                if (! $storage->exists($file)) {
                                    return null;
                                }
                            } catch (UnableToCheckFileExistence $exception) {
                                return null;
                            }

                            if (config('filament.default_filesystem_disk') === 'r2') {
                                return $storage->url($file);
                            } else {
                                if ($storage->getVisibility($file) === 'private') {
                                    try {
                                        return $storage->temporaryUrl(
                                            $file,
                                            now()->addMinutes(5),
                                        );
                                    } catch (\Throwable $exception) {
                                        // This driver does not support creating temporary URLs.
                                    }
                                }

                            }
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
                Tables\Columns\BadgeColumn::make('gateway')
                    ->formatStateUsing(fn ($state) => Str::headline($state))
                    ->sortable()
                    ->searchable(),
                Tables\Columns\IconColumn::make('status')
                    ->label(trans('Enabled'))
                    ->options([
                        'heroicon-o-check-circle' => fn ($state) => $state == true,
                        'heroicon-o-x-circle' => fn ($state) => $state === false,
                    ])
                    ->color(fn ($state) => $state == true ? 'success' : 'danger'),
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
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
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
            'index' => Pages\ListPaymentMethods::route('/'),
            'create' => Pages\CreatePaymentMethod::route('/create'),
            'edit' => Pages\EditPaymentMethod::route('/{record}/edit'),
        ];
    }
}
