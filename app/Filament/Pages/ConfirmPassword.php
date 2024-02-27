<?php

declare(strict_types=1);

namespace App\Filament\Pages;

use DanHarrin\LivewireRateLimiting\Exceptions\TooManyRequestsException;
use DanHarrin\LivewireRateLimiting\WithRateLimiting;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Facades\Filament;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Concerns\InteractsWithFormActions;
use Filament\Pages\SimplePage;
use Illuminate\Support\Facades\Session;

/**
 * @property \Filament\Forms\ComponentContainer $form
 */
class ConfirmPassword extends SimplePage
{
    use InteractsWithFormActions;
    use WithRateLimiting;

    protected static string $view = 'filament.auth.confirm-password';

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill();
    }

    public function confirm(): mixed
    {
        try {
            $this->rateLimit(2);
        } catch (TooManyRequestsException $exception) {
            Notification::make()
                ->title(trans('filament-panels::pages/auth/password-reset/reset-password.notifications.throttled.title', [
                    'seconds' => $exception->secondsUntilAvailable,
                    'minutes' => ceil($exception->secondsUntilAvailable / 60),
                ]))
                ->body(trans('filament-panels::pages/auth/password-reset/reset-password.notifications.throttled.body', [
                    'seconds' => $exception->secondsUntilAvailable,
                    'minutes' => ceil($exception->secondsUntilAvailable / 60),
                ]))
                ->danger()
                ->send();

            return null;
        }

        $this->form->getState();

        // already done via `currentPassword()` rule

        // see \Illuminate\Auth\Middleware\RequirePassword::shouldConfirmPassword()
        Session::put('auth.password_confirmed_at', time());

        return redirect()->intended(Filament::getUrl());
    }

    public function form(Form $form): Form
    {
        return $form;
    }

    protected function getForms(): array
    {
        return [
            'form' => $this->form(
                $this->makeForm()
                    ->schema([
                        TextInput::make('password')
                            ->default('')
                            ->password()
                            ->required()
                            ->currentPassword()
                            ->autofocus(),
                    ])
                    ->statePath('data'),
            ),
        ];
    }

    /**
     * @return array<Action | ActionGroup>
     */
    protected function getFormActions(): array
    {
        return [
            Action::make('save')
                ->label(trans('Confirm'))
                ->submit('save'),
            Action::make('back')
                ->label(trans('Go back'))
                ->url(Filament::getUrl())
                ->color('gray'),
        ];
    }

    protected function hasFullWidthFormActions(): bool
    {
        return true;
    }
}
