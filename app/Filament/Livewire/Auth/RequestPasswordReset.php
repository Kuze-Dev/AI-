<?php

declare(strict_types=1);

namespace App\Filament\Livewire\Auth;

use Domain\Auth\Actions\ForgotPasswordAction;
use Filament\Facades\Filament;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Livewire\Component;
use Livewire\Redirector;

/**
 * @property \Filament\Forms\ComponentContainer $form
 */
class RequestPasswordReset extends Component implements HasForms
{
    use InteractsWithForms;

    public string $email;

    public function mount(): void
    {
        $this->form->fill(['email' => '']);
    }

    public function sendResetPasswordRequest(): Redirector|RedirectResponse
    {
        $this->form->validate();

        $result = app(ForgotPasswordAction::class)->execute($this->email, 'admin');

        $result->throw();

        Notification::make()
            ->title($result->getMessage())
            ->success()
            ->send();

        return redirect()->intended(Filament::getUrl());
    }

    protected function getFormSchema(): array
    {
        return [
            TextInput::make('email')
                ->default('')
                ->email()
                ->required()
                ->autocomplete(),
        ];
    }

    public function render(): View
    {
        return view('filament.auth.request-password-reset')
            ->layout('filament::components.layouts.card', [
                'title' => trans('Reset password'),
            ]);
    }
}
