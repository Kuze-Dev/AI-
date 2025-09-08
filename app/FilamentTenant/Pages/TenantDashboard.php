<?php

declare(strict_types=1);

namespace App\FilamentTenant\Pages;

use App\Features\CMS\OpenAI;
use Domain\Tenant\TenantFeatureSupport;
use Filament\Pages\Dashboard as BaseDashboard;
use Filament\Forms;
use Filament\Actions\Action;
use Illuminate\Support\Facades\Log;

class TenantDashboard extends BaseDashboard
{
    protected static string $view = 'filament-tenant.pages.dashboard';

    protected function getHeaderActions(): array
    {
        $actions = parent::getHeaderActions();

        if (TenantFeatureSupport::active(OpenAI::class)) {
            $actions[] = Action::make('uploadFile')
                ->label('Upload File')
                ->icon('heroicon-o-arrow-up-tray')
                ->color('primary')
                ->form([
                    Forms\Components\FileUpload::make('file')
                        ->label('Choose a file')
                        ->disk('public')
                        ->directory('uploads')
                        ->required(),
                ])
                ->action(function (array $data): void {
                    Log::info('Uploaded file:', $data);
                    // You can process or save the file here
                });
        }

        return $actions;
    }
}