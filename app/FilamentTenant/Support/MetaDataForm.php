<?php

declare(strict_types=1);

namespace App\FilamentTenant\Support;

use Filament\Forms;
use Filament\Forms\Components\Section;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Throwable;

/**
 * @deprecated use MetaDataFormV2
 */
class MetaDataForm extends Section
{
    #[\Override]
    public function setUp(): void
    {
        parent::setUp();

        $this->statePath('meta_data');

        $this->schema([
            Forms\Components\TextInput::make('title')
                ->string()
                ->maxLength(255)
                ->formatStateUsing(fn ($record) => $record?->metaData?->title),
            Forms\Components\TextInput::make('keywords')
                ->string()
                ->maxLength(255)
                ->formatStateUsing(fn ($record) => $record?->metaData?->keywords),
            Forms\Components\TextInput::make('author')
                ->string()
                ->maxLength(255)
                ->formatStateUsing(fn ($record) => $record?->metaData?->author),
            Forms\Components\Textarea::make('description')
                ->maxLength(fn (int $value = 160) => $value)
                ->formatStateUsing(fn ($record) => $record?->metaData?->description),
            Forms\Components\FileUpload::make('image')
                ->formatStateUsing(function ($record) {
                    return $record?->metaData?->getMedia('image')
                        ->mapWithKeys(fn (Media $file) => [$file->uuid => $file->uuid])
                        ->toArray() ?? [];
                })
                // ->image()
                ->acceptedFileTypes([
                    'image/jpg',
                    'image/jpeg',
                    'image/png',
                    'image/bmp',
                    'image/tiff',
                ])
                // ->beforeStateDehydrated(null)
                ->dehydrateStateUsing(fn (?array $state) => array_values($state ?? [])[0] ?? null)
                ->getUploadedFileUsing(static function (Forms\Components\FileUpload $component, string $file): ?string {
                    $mediaClass = config()->string('media-library.media_model', Media::class);

                    /** @var ?Media $media */
                    $media = $mediaClass::findByUuid($file);

                    if ($component->getVisibility() === 'private') {
                        try {
                            return $media?->getTemporaryUrl(now()->addMinutes(5));
                        } catch (Throwable) {
                            // This driver does not support creating temporary URLs.
                        }
                    }

                    return $media?->getUrl();
                }),
            Forms\Components\TextInput::make('image_alt_text')
                ->visible(fn (\Filament\Forms\Get $get) => filled($get('image')))
                ->formatStateUsing(fn ($record) => $record?->metaData?->getFirstMedia('image')?->getCustomProperty('alt_text')),
        ]);
    }
}
