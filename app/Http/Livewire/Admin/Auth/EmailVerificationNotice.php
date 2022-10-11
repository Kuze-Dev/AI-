<?php

declare(strict_types=1);

namespace App\Http\Livewire\Admin\Auth;

use Filament\Facades\Filament;
use Filament\Notifications\Notification;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class EmailVerificationNotice extends Component
{
    public function resendEmailVerification(): void
    {
        /** @var MustVerifyEmail $user */
        $user = Auth::user();

        $user->sendEmailVerificationNotification();

        Notification::make()
            ->success()
            ->title('A fresh verification link has been sent to your email address.')
            ->send();
    }

    public function logout(): void
    {
        Auth::logout();

        redirect()->intended(Filament::getUrl());
    }

    public function render(): View
    {
        return view('livewire.admin.auth.resend-email-verification')
            ->layout('filament::components.layouts.card', [
                'title' => trans('Verify Your Email Address'),
            ]);
    }
}
