<?php

declare(strict_types=1);

namespace App\Filament\Livewire\Auth;

use Domain\Auth\Actions\LoginAction;
use Domain\Auth\DataTransferObjects\LoginData;
use Domain\Auth\Enums\LoginResult;
use Filament\Facades\Filament;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Livewire\Component;
use Livewire\Redirector;

/**
 * @property \Filament\Forms\ComponentContainer $form
 */
class Login extends Component implements HasForms
{
    use InteractsWithForms;

    public string $email = '';

    public string $password = '';

    public bool $remember = false;

    public function mount(): void
    {
        if (Filament::auth()->check()) {
            redirect()->intended(Filament::getUrl());
        }

        $this->form->fill();
    }

    public function authenticate(): Redirector|RedirectResponse
    {
        $this->form->validate();

        $result = app(LoginAction::class)->execute(new LoginData(
            email: $this->email,
            password: $this->password,
            remember: $this->remember,
            guard: 'admin',
        ));

        return match ($result) {
            LoginResult::TWO_FACTOR_REQUIRED => $this->redirectToTwoFactorAuthentication(),
            LoginResult::SUCCESS => redirect()->intended(Filament::getUrl()),
        };
    }

    public function redirectToTwoFactorAuthentication(): Redirector|RedirectResponse
    {
        return redirect()->route('filament.auth.two-factor');
    }

    public function redirectToRequestPasswordReset(): Redirector|RedirectResponse
    {
        return redirect()->route('filament.auth.password.request');
    }

    protected function getFormSchema(): array
    {
        return [
            TextInput::make('email')
                ->label(trans('filament::login.fields.email.label'))
                ->email()
                ->required()
                ->autocomplete()
                ->default(''),
            TextInput::make('password')
                ->label(trans('filament::login.fields.password.label'))
                ->password()
                ->required()
                ->default(''),
            Checkbox::make('remember')
                ->label(trans('filament::login.fields.remember.label')),
        ];
    }

    public function render(): View
    {
        return view('filament.auth.login')
            ->layout('filament::components.layouts.login-card', [
                'title' => __('filament::login.title'),
            ]);
    }
}
