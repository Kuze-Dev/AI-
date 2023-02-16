<?php

declare(strict_types=1);

namespace App\FilamentTenant\Support;

use Filament\Forms;
use Filament\Forms\Components\Section;
use Livewire\TemporaryUploadedFile;

class MetaDataForm extends Section
{
    public function setUp(): void
    {
        parent::setUp();

        $this->statePath('meta_data');

        $this->afterStateHydrated(fn ($component, $record) => $component->state($record?->metaData?->toArray() ?? []));

        $this->schema([
            Forms\Components\TextInput::make('title')
                ->lazy()
                ->label('Title'),
            Forms\Components\TextInput::make('keywords')
                ->lazy()
                ->label('Keywords'),
            Forms\Components\TextInput::make('author')
                ->lazy()
                ->label('Author'),
            Forms\Components\Textarea::make('description')
                ->lazy()
                ->label('Description'),
            Forms\Components\FileUpload::make('image')
                ->label('Image')
                ->acceptedFileTypes(['image/png', 'image/webp', 'image/jpg', 'image/jpeg'])
                ->maxSize(1_000)
                ->getUploadedFileNameForStorageUsing(static function (TemporaryUploadedFile $file) {
                    return 'image.'.$file->extension();
                }),
        ]);
    }
}
