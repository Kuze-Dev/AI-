<?php

declare(strict_types=1);

namespace App\Http\Livewire\Admin\Auth;

use Domain\Auth\Actions\ResetPasswordAction;
use Domain\Auth\DataTransferObjects\ResetPasswordData;
use Filament\Facades\Filament;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rules\Password;
use Livewire\Component;
use Livewire\Redirector;

/**
 * @property \Filament\Forms\ComponentContainer $form
 */
class ResetPassword extends Component implements HasForms
{
    use InteractsWithForms;

    public string $email = '';

    public string $password = '';

    public string $password_confirmation = '';

    public string $token = '';

    public function mount(Request $request): void
    {
        $this->form->fill([
            'email' => $request->get('email'),
            'token' => (string) $request->route('token'),
        ]);
    }

    public function resetPassword(): Redirector|RedirectResponse
    {
        $this->validate();

        $result = app(ResetPasswordAction::class)->execute(
            new ResetPasswordData(
                email: $this->email,
                password: $this->password,
                token: $this->token,
            ),
            'admin'
        );

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
                ->disabled(),
            TextInput::make('password')
                ->default('')
                ->password()
                ->required()
                ->rule(Password::default())
                ->autocomplete('new-password'),
            TextInput::make('password_confirmation')
                ->default('')
                ->required()
                ->password()
                ->same('password')
                ->autocomplete('new-password'),
        ];
    }

    public function render(): View
    {
        return view('livewire.admin.auth.reset-password')
            ->layout('filament::components.layouts.card', [
                'title' => trans('Reset password'),
            ]);
    }
}
