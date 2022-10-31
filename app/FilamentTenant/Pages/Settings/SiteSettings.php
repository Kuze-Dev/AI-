<?php

declare(strict_types=1);

namespace App\FilamentTenant\Pages\Settings;

use App\Filament\Pages\Settings\SiteSettings as BaseSiteSettings;
use App\FilamentTenant\Pages\Settings\Concerns\ContextualSettingsPage;
use Filament\Forms\Components\Card;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;

class SiteSettings extends BaseSiteSettings
{
    use ContextualSettingsPage;

    protected function getFormSchema(): array
    {
        return [
            Card::make([
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
                    ->image()
                    ->acceptedFileTypes(['image/png', 'image/webp', 'image/jpg', 'image/jpeg'])
                    ->maxSize(1000)
                    ->required()
                    ->getUploadedFileUrlUsing(fn (string $file) => tenant_asset($file)),
                FileUpload::make('favicon')
                    ->image()
                    ->acceptedFileTypes(['image/ico', 'image/png', 'image/webp', 'image/jpg', 'image/jpeg'])
                    ->imageResizeTargetHeight('100')
                    ->imageResizeTargetWidth('100')
                    ->maxSize(1000)
                    ->required()
                    ->getUploadedFileUrlUsing(fn (string $file) => tenant_asset($file)),
            ])
                ->columns(2),
        ];
    }
}
