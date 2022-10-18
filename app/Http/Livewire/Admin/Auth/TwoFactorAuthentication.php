<?php

declare(strict_types=1);

namespace App\Http\Livewire\Admin\Auth;

use Domain\Auth\Actions\AuthenticateTwoFactorAction;
use Domain\Auth\DataTransferObjects\TwoFactorData;
use Filament\Facades\Filament;
use Filament\Forms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\HtmlString;
use Illuminate\Validation\ValidationException;
use Livewire\Component;
use Livewire\Redirector;

/**
 * @property \Filament\Forms\ComponentContainer $form
 */
class TwoFactorAuthentication extends Component implements HasForms
{
    use InteractsWithForms;

    public string $code;

    public string $recovery_code;

    public bool $remember = false;

    public string $method;

    public function mount(): void
    {
        if ( ! Session::has('login.id')) {
            redirect()->intended(Filament::getUrl());
        }

        $this->form->fill([
            'code' => '',
            'recovery_code' => '',
            'method' => '',
        ]);
    }

    public function goBack(): Redirector|RedirectResponse
    {
        return redirect()->intended(Filament::getUrl());
    }

    public function verify(): Redirector|RedirectResponse
    {
        $this->form->validate();

        $result = app(AuthenticateTwoFactorAction::class)->execute($this->buildTwoFactorData());

        if ( ! $result) {
            throw ValidationException::withMessages(['code' => trans('Invalid code.')]);
        }

        return redirect()->intended(Filament::getUrl());
    }

    protected function buildTwoFactorData(): TwoFactorData
    {
        return match ($this->method) {
            'otp' => new TwoFactorData(
                code: $this->code,
                remember_device: $this->remember,
                guard: 'admin'
            ),
            'recovery_code' => new TwoFactorData(
                recovery_code: $this->recovery_code,
                remember_device: $this->remember,
                guard: 'admin'
            ),
            default => throw ValidationException::withMessages(['method' => 'Invalid method selected']),
        };
    }

    protected function getFormSchema(): array
    {
        return [
            Forms\Components\Wizard::make(fn () => array_filter([
                Forms\Components\Wizard\Step::make('Select method')
                    ->schema([
                        Forms\Components\Radio::make('method')
                            ->label('Choose a way to sign in')
                            ->options([
                                'otp' => 'Via OTP',
                                'recovery_code' => 'Via Recovery Code',
                            ])
                            ->required(),
                    ]),
                match ($this->method) {
                    'otp' => Forms\Components\Wizard\Step::make('Via OTP')
                        ->schema([
                            Forms\Components\TextInput::make('code')
                                ->label(trans('Code'))
                                ->required(),
                            Forms\Components\Checkbox::make('remember')
                                ->label(trans('Remember this device')),
                        ]),
                    'recovery_code' => Forms\Components\Wizard\Step::make('Via Recovery Code')
                        ->schema([
                            Forms\Components\TextInput::make('recovery_code')
                                ->label(trans('Recovery Code'))
                                ->required(),
                            Forms\Components\Checkbox::make('remember')
                                ->label(trans('Remember this device')),
                        ]),
                    default => null
                },
            ]))
                ->reactive()
                ->cancelAction(new HtmlString(view('livewire.admin.auth.partials.two-factor-authentication-cancel')->render()))
                ->submitAction(new HtmlString(view('livewire.admin.auth.partials.two-factor-authentication-submit')->render()))
        ];
    }

    public function render(): View
    {
        return view('livewire.admin.auth.two-factor-authentication')
            ->layout('filament::components.layouts.card', [
                'title' => trans('Two Factor Authentication'),
            ]);
    }
}
