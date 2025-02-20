<?php

declare(strict_types=1);

namespace App\Filament\Pages;

use Domain\Auth\Contracts\HasActiveState;
use Filament\Facades\Filament;
use Filament\Pages\SimplePage;

class AccountDeactivatedNotice extends SimplePage
{
    protected static string $view = 'filament.auth.account-deactivated-notice';

    public function mount(): void
    {
        if (filament_admin()->isActive()) {
            redirect()->intended(Filament::getUrl());
        }
    }
}
