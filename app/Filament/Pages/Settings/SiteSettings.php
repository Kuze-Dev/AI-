<?php

declare(strict_types=1);

namespace App\Filament\Pages\Settings;

use App\Settings\SiteSettings as ManageSiteSettings;
use Filament\Forms\Components\Card;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;

class SiteSettings extends BaseSettings
{
    protected static string $settings = ManageSiteSettings::class;

    protected static ?string $navigationIcon = 'heroicon-o-globe-alt';

    protected function getFormSchema(): array
    {
        return [
            Card::make([
                TextInput::make('site_name')
                    ->label('Site Name')
                    ->required()
                    ->maxLength(100)
                    ->columnSpan('full'),
                Textarea::make('site_description')
                    ->label('Description')
                    ->required()
                    ->maxLength(255)
                    ->columnSpan('full'),
                TextInput::make('site_author')
                    ->label('Author')
                    ->required()
                    ->maxLength(100)
                    ->columnSpan('full'),
                FileUpload::make('site_logo')
                    ->label('Logo')
                    ->image()
                    ->acceptedFileTypes(['image/png', 'image/webp', 'image/jpg', 'image/jpeg'])
                    ->maxSize(1000)
                    ->required(),
                FileUpload::make('site_favicon')
                    ->image()
                    ->acceptedFileTypes(['image/ico', 'image/png', 'image/webp', 'image/jpg', 'image/jpeg'])
                    ->label('Favicon')
                    ->imageResizeTargetHeight('100')
                    ->imageResizeTargetWidth('100')
                    ->maxSize(1000)
                    ->required(),
            ])
                ->columns(2),
        ];
    }
}
