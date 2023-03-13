<?php

declare(strict_types=1);

namespace App\FilamentTenant\Support;

use Filament\Forms;
use Filament\Forms\Components\Section;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Throwable;

class MetaDataForm extends Section
{
    public function setUp(): void
    {
        parent::setUp();

        $this->statePath('meta_data');

        $this->schema([
            Forms\Components\TextInput::make('title')
                ->afterStateHydrated(fn ($component, $record) => $component->state($record?->metaData?->title)),
            Forms\Components\TextInput::make('keywords')
                ->afterStateHydrated(fn ($component, $record) => $component->state($record?->metaData?->keywords)),
            Forms\Components\TextInput::make('author')
                ->afterStateHydrated(fn ($component, $record) => $component->state($record?->metaData?->author)),
            Forms\Components\Textarea::make('description')
                ->afterStateHydrated(fn ($component, $record) => $component->state($record?->metaData?->description)),
            Forms\Components\FileUpload::make('image')
                ->formatStateUsing(function ($record) {
                    return $record->metaData->getMedia('image')
                        ->mapWithKeys(fn (Media $file) => [$file->uuid => $file->uuid])
                        ->toArray();
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
                })
                ->deleteUploadedFileUsing(static function (Forms\Components\FileUpload $component, string $file) {
                    if ( ! $file) {
                        return;
                    }

                    $component->state([]);
                }),
        ]);
    }
}
