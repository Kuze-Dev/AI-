<?php

declare(strict_types=1);

namespace App\Filament\Livewire\Auth;

use Domain\Auth\Contracts\HasActiveState;
use Filament\Facades\Filament;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\Redirector;

class AccountDeactivatedNotice extends Component
{
    public function mount(): void
    {
        $user = Filament::auth()->user();

        if ($user instanceof HasActiveState && $user->isActive()) {
            redirect()->intended(Filament::getUrl());
        }
    }

    public function logout(): Redirector|RedirectResponse
    {
        Auth::logout();

        return redirect()->intended(Filament::getUrl());
    }

    public function render(): View
    {
        return view('filament.auth.account-deactivated-notice')
            ->layout('filament::components.layouts.card', [
                'title' => trans('Account Deactivated'),
            ]);
    }
}
