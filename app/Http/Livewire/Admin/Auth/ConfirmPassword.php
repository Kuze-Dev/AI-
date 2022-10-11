<?php

namespace App\Http\Livewire\Admin\Auth;

use Domain\Auth\Actions\ConfirmPasswordAction;
use Filament\Facades\Filament;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Illuminate\Contracts\View\View;
use Illuminate\Validation\ValidationException;
use Livewire\Component;

/**
 * @property \Filament\Forms\ComponentContainer $form
 */
class ConfirmPassword extends Component implements HasForms
{
    use InteractsWithForms;

    public string $password;

    public function mount(): void
    {
        $this->form->fill();
    }

    public function confirm(): void
    {
        $confirmed = app(ConfirmPasswordAction::class)->execute($this->password, 'admin');

        if (! $confirmed) {
            throw ValidationException::withMessages([
                'password' => trans('auth.failed'),
            ]);
        }

        redirect()->intended(Filament::getUrl());
    }

    protected function getFormSchema(): array
    {
        return [
            TextInput::make('password')
                ->default('')
                ->password()
                ->required(),
        ];
    }

    public function render(): View
    {
        return view('livewire.admin.auth.confirm-password')
            ->layout('filament::components.layouts.card', [
                'title' => trans('Confirm Access'),
            ]);
    }
}
