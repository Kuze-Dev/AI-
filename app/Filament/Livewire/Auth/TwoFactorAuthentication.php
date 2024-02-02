<?php

declare(strict_types=1);

namespace App\Filament\Livewire\Auth;

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
use Livewire\Features\SupportRedirects\Redirector;

/**
 * @property \Filament\Forms\ComponentContainer $form
 */
class TwoFactorAuthentication extends Component implements HasForms
{
    use InteractsWithForms;

    public string $code = '';

    public string $recovery_code = '';

    public bool $remember_device = false;

    public function mount(): void
    {
        if (! Session::has('login.id')) {
            redirect()->intended(Filament::getUrl());
        }

        $this->form->fill();
    }

    public function goBack(): Redirector|RedirectResponse
    {
        return redirect()->intended(Filament::getUrl());
    }

    public function verify(): Redirector|RedirectResponse
    {
        $result = app(AuthenticateTwoFactorAction::class)->execute($this->buildTwoFactorData());

        if (! $result) {
            throw ValidationException::withMessages([
                'code' => trans('Invalid code.'),
                'recovery_code' => trans('Invalid code.'),
            ]);
        }

        return redirect()->intended(Filament::getUrl());
    }

    protected function buildTwoFactorData(): TwoFactorData
    {
        $data = array_filter($this->form->getState(), fn (mixed $value) => filled($value));

        $data['guard'] = 'admin';

        return new TwoFactorData(...$data);
    }

    protected function getFormSchema(): array
    {
        return [
            Forms\Components\Wizard::make(fn (\Filament\Forms\Get $get) => array_filter([
                Forms\Components\Wizard\Step::make('Select method')
                    ->schema([
                        Forms\Components\Radio::make('method')
                            ->label('Choose a way to sign in')
                            ->options([
                                'otp' => 'Via OTP',
                                'recovery_code' => 'Via Recovery Code',
                            ])
                            ->afterStateUpdated(fn (\Filament\Forms\Get $get, \Filament\Forms\Set $set) => match ($get('method')) {
                                'otp' => $set('recovery_code', ''),
                                'recovery_code' => $set('code', ''),
                                default => null,
                            })
                            ->required()
                            ->dehydrated(false),
                    ]),
                match ($get('method')) {
                    'otp' => Forms\Components\Wizard\Step::make('Via OTP')
                        ->schema([
                            Forms\Components\TextInput::make('code')
                                ->label(trans('Code'))
                                ->required()
                                ->default(''),
                            Forms\Components\Checkbox::make('remember_device')
                                ->statePath('remember_device')
                                ->label(trans('Remember this device')),
                        ]),
                    'recovery_code' => Forms\Components\Wizard\Step::make('Via Recovery Code')
                        ->schema([
                            Forms\Components\TextInput::make('recovery_code')
                                ->label(trans('Recovery Code'))
                                ->required()
                                ->default(''),
                            Forms\Components\Checkbox::make('remember_device')
                                ->statePath('remember_device')
                                ->label(trans('Remember this device')),
                        ]),
                    default => null
                },
            ]))
                ->reactive()
                ->cancelAction(new HtmlString(view('filament.auth.partials.two-factor-authentication-cancel')->render()))
                ->submitAction(new HtmlString(view('filament.auth.partials.two-factor-authentication-submit')->render())),
        ];
    }

    public function render(): View
    {
        return view('filament.auth.two-factor-authentication')
            ->layout('filament::components.layouts.card', [
                'title' => trans('Two Factor Authentication'),
            ]);
    }
}
