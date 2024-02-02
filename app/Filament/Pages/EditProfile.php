<?php

declare(strict_types=1);

namespace App\Filament\Pages;

use Domain\Admin\Models\Admin;
use Filament\Facades\Filament;
use Filament\Forms;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\Rule;

class EditProfile extends \Filament\Pages\Auth\EditProfile
{
    public static function canAccess(): bool
    {
        return ! Filament::auth()->user()?->isZeroDayAdmin();
    }

    protected function getForms(): array
    {
        /** @var Forms\Components\TextInput $emailTextInput */
        $emailTextInput = $this->getEmailFormComponent();
        /** @var Forms\Components\TextInput $passwordTextInput */
        $passwordTextInput = $this->getPasswordFormComponent();

        return [
            'form' => $this->form(
                $this->makeForm()
                    ->schema([
                        Forms\Components\TextInput::make('first_name')
                            ->required()
                            ->string(),
                        Forms\Components\TextInput::make('last_name')
                            ->required()
                            ->string(),
                        $emailTextInput
                            ->rules(Rule::email())
                            ->helperText(
                                ! config('domain.admin.can_change_email')
                                ? 'Email update is currently disabled.'
                                : null
                            )
                            ->disabled(! config('domain.admin.can_change_email'))
                            ->dehydrated((bool) config('domain.admin.can_change_email')),
                        $passwordTextInput
                            ->helperText(
                                app()->environment('local', 'testing')
                                    ? trans('Password must be at least 4 characters.')
                                    : trans('Password must be at least 8 characters, have 1 special character, 1 number, 1 upper case and 1 lower case.')
                            ),
                        $this->getPasswordConfirmationFormComponent(),
                        Forms\Components\Select::make('timezone')
                            ->options(
                                collect(timezone_identifiers_list())
                                    ->mapWithKeys(fn (string $timezone) => [$timezone => $timezone])
                                    ->toArray()
                            )
                            ->rules(['nullable', 'timezone'])
                            ->searchable(),
                    ])
                    ->operation('edit')
                    ->model($this->getUser())
                    ->statePath('data')
                    ->inlineLabel(! static::isSimple()),
            ),
        ];
    }

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        /** @var Admin $admin */
        $admin = parent::handleRecordUpdate($record, array_filter($data, fn ($value) => filled($value)));

        if ($admin->wasChanged('email')) {
            $admin->forceFill(['email_verified_at' => null])
                ->save();

            $admin->sendEmailVerificationNotification();
        }

        return $admin;
    }
}
