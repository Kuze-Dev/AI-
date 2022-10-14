<?php

declare(strict_types=1);

namespace App\Http\Livewire\Admin\Auth;

use Domain\Auth\Actions\ForgotPasswordAction;
use Filament\Facades\Filament;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Illuminate\Contracts\View\View;
use Livewire\Component;

/**
 * @property \Filament\Forms\ComponentContainer $form
 */
class RequestPasswordReset extends Component implements HasForms
{
    use InteractsWithForms;

    public string $email;

    public function mount(): void
    {
        $this->form->fill();
    }

    public function sendResetPasswordRequest(): void
    {
        $result = app(ForgotPasswordAction::class)
            ->execute($this->email, 'admin');

        $result->throw();

        Notification::make()
            ->title($result->getMessage())
            ->success()
            ->send();

        redirect()->intended(Filament::getUrl());
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
        return view('livewire.admin.auth.request-password-reset')
            ->layout('filament::components.layouts.card', [
                'title' => trans('Reset password'),
            ]);
    }
}
