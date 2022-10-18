<?php

declare(strict_types=1);

namespace App\Http\Livewire\Admin\Auth;

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

    public string $email;

    public string $password;

    public bool $remember = false;

    public function mount(): void
    {
        $this->form->fill([
            'email' => '',
            'password' => '',
        ]);
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
            LoginResult::TWO_FACTOR_REQUIRED => redirect()->route('admin.two-factor'),
            LoginResult::SUCCESS => redirect()->intended(Filament::getUrl()),
        };
    }

    protected function getFormSchema(): array
    {
        return [
            TextInput::make('email')
                ->label(__('filament::login.fields.email.label'))
                ->email()
                ->required()
                ->autocomplete(),
            TextInput::make('password')
                ->label(__('filament::login.fields.password.label'))
                ->password()
                ->required(),
            Checkbox::make('remember')
                ->label(__('filament::login.fields.remember.label')),
        ];
    }

    public function render(): View
    {
        return view('livewire.admin.auth.login')
            ->layout('filament::components.layouts.card', [
                'title' => __('filament::login.title'),
            ]);
    }
}
