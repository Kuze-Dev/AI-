<?php

declare(strict_types=1);

namespace Domain\Support\MetaTag;
use Filament\Forms;
use Livewire\TemporaryUploadedFile;

class MetaTagsForm 
{
    public static function formBuilder()
    {
        return Forms\Components\Section::make('Meta Tags')
            ->schema([
                Forms\Components\TextInput::make('meta_title')
                    ->label('Title'),
                Forms\Components\TextInput::make('meta_keywords')
                    ->label('Keywords'),
                Forms\Components\TextInput::make('meta_author')
                    ->label('Author'),
                Forms\Components\Textarea::make('meta_description')
                    ->label('Description'),
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