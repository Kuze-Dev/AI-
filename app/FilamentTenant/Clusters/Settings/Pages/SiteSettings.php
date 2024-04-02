<?php

declare(strict_types=1);

namespace App\FilamentTenant\Clusters\Settings\Pages;

use App\Filament\Rules\FullyQualifiedDomainNameRule;
use App\FilamentTenant\Support\Concerns\AuthorizeCMSSettings;
use App\Settings\SiteSettings as ManageSiteSettings;
use Filament\Forms\Components\Card;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Illuminate\Support\Facades\Cache;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

class SiteSettings extends TenantBaseSettings
{
    use AuthorizeCMSSettings;

    protected static string $settings = ManageSiteSettings::class;

    protected static ?string $navigationIcon = 'heroicon-o-globe-alt';

    #[\Override]
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
                    ->acceptedFileTypes(['image/png', 'image/webp', 'image/jpg', 'image/jpeg'])
                    ->maxSize(1_000)
                    ->required()
                    ->getUploadedFileNameForStorageUsing(static function (TemporaryUploadedFile $file) {
                        return 'logo.'.$file->extension();
                    }),
                FileUpload::make('favicon')
                    ->acceptedFileTypes(['image/ico', 'image/png', 'image/webp', 'image/jpg', 'image/jpeg'])
                    ->imageResizeTargetHeight('100')
                    ->imageResizeTargetWidth('100')
                    ->maxSize(1_000)
                    ->required()
                    ->getUploadedFileNameForStorageUsing(static function (TemporaryUploadedFile $file) {
                        return 'favicon.'.$file->extension();
                    }),
                TextInput::make('front_end_domain')
                    ->required()
                    ->rules([new FullyQualifiedDomainNameRule()])
                    ->maxLength(100)
                    ->columnSpan('full'),
            ])
                ->columns(2),
        ];
    }

    #[\Override]
    protected function afterSave(): void
    {
        Cache::flush();
    }
}
