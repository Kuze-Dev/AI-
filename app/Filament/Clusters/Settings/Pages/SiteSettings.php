<?php

declare(strict_types=1);

namespace App\Filament\Clusters\Settings\Pages;

use App\Settings\SiteSettings as ManageSiteSettings;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

class SiteSettings extends BaseSettings
{
    protected static string $settings = ManageSiteSettings::class;

    protected static ?string $navigationIcon = 'heroicon-o-globe-alt';

    #[\Override]
    protected function getFormSchema(): array
    {
        return [
            Section::make([
                TextInput::make('name')
                    ->required()
                    ->maxLength(100)
                    ->columnSpan('full'),
                Textarea::make('description')
                    ->required()
                    ->maxLength(255)
                    ->columnSpan('full'),
                TextInput::make('author')
                    ->required()
                    ->maxLength(100)
                    ->columnSpan('full'),
                FileUpload::make('logo')
                    ->acceptedFileTypes(['image/png', 'image/webp', 'image/jpg', 'image/jpeg'])
                    ->maxSize(1_000)
                    ->required()
                    ->getUploadedFileNameForStorageUsing(static fn (TemporaryUploadedFile $file) => 'logo.'.$file->extension()),
                FileUpload::make('favicon')
                    ->acceptedFileTypes(['image/ico', 'image/png', 'image/webp', 'image/jpg', 'image/jpeg'])
                    ->imageResizeTargetHeight('100')
                    ->imageResizeTargetWidth('100')
                    ->maxSize(1_000)
                    ->required()
                    ->getUploadedFileNameForStorageUsing(static fn (TemporaryUploadedFile $file) => 'favicon.'.$file->extension()),
            ])
                ->columns(2),
        ];
    }
}
