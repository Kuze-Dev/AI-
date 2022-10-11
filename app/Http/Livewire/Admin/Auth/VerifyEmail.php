<?php

declare(strict_types=1);

namespace App\Http\Livewire\Admin\Auth;

use App\Http\Requests\Admin\Auth\VerifyEmailRequest;
use Domain\Auth\Actions\VerifyEmailAction;
use Filament\Facades\Filament;
use Filament\Notifications\Notification;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Livewire\Component;

class VerifyEmail extends Component
{
    public function mount(VerifyEmailRequest $request): void
    {
        $user = $request->user();

        if ( ! $user instanceof MustVerifyEmail) {
            redirect()->intended(Filament::getUrl());

            return;
        }

        $result = app(VerifyEmailAction::class)->execute($user);

        if ($result) {
            Notification::make()
                ->title(trans('You are now verified!'))
                ->success()
                ->send();
        }

        redirect()->intended(Filament::getUrl());
    }
}
