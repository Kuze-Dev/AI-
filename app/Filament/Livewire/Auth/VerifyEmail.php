<?php

declare(strict_types=1);

namespace App\Filament\Livewire\Auth;

use App\Filament\Requests\VerifyEmailRequest;
use Domain\Auth\Actions\VerifyEmailAction;
use Filament\Facades\Filament;
use Filament\Notifications\Notification;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Http\RedirectResponse;
use Livewire\Component;
use Livewire\Redirector;

class VerifyEmail extends Component
{
    public function mount(VerifyEmailRequest $request): Redirector|RedirectResponse
    {
        $user = $request->user();

        if ( ! $user instanceof MustVerifyEmail) {
            throw new AuthorizationException();
        }

        $result = app(VerifyEmailAction::class)->execute($user);

        if ($result) {
            Notification::make()
                ->title(trans('You are now verified!'))
                ->success()
                ->send();
        }

        return redirect()->intended(Filament::getUrl());
    }
}
