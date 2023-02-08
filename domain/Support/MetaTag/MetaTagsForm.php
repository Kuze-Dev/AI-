<?php

declare(strict_types=1);

namespace Domain\Support\MetaTag;

use Closure;
use Filament\Forms;
use Livewire\TemporaryUploadedFile;

class MetaTagsForm 
{
    public static function formBuilder()
    {
        return Forms\Components\Section::make('Meta Tags')
            ->schema([
                Forms\Components\TextInput::make('meta_title')
                    ->unique(ignoreRecord: true)
                    ->lazy()
                    ->label('Title')
                    ->afterStateHydrated(function ($component, $record): void {
                        $component->state($record ? $record->metaTags->first()->title : '');
                    }),
                Forms\Components\TextInput::make('meta_keywords')
                    ->unique(ignoreRecord: true)
                    ->lazy()
                    ->label('Keywords')
                    ->afterStateHydrated(function ($component, $record): void {
                        $component->state($record ? $record->metaTags->first()->keywords : '');
                    }),
                Forms\Components\TextInput::make('meta_author')
                    ->unique(ignoreRecord: true)
                    ->lazy()
                    ->label('Author')
                    ->afterStateHydrated(function ($component, $record): void {
                        $component->state($record ? $record->metaTags->first()->author : '');
                    }),
                Forms\Components\Textarea::make('meta_description')
                    ->unique(ignoreRecord: true)
                    ->lazy()
                    ->label('Description')
                    ->afterStateHydrated(function ($component, $record): void {
                        $component->state($record ? $record->metaTags->first()->description : '');
                    }),
                Forms\Components\FileUpload::make('meta_image')
                    ->label('Image')
                    ->acceptedFileTypes(['image/png', 'image/webp', 'image/jpg', 'image/jpeg'])
                    ->maxSize(1_000)
                    ->getUploadedFileNameForStorageUsing(static function (TemporaryUploadedFile $file) {
                        return 'image.'.$file->extension();
                    })
            ]);
    }
}