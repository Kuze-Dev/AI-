<?php

declare(strict_types=1);

namespace App\Filament\Pages\Auth;

use App\Filament\Pages\Concerns\LogsFormActivity;
use Domain\Admin\Actions\UpdateAdminAction;
use Domain\Admin\DataTransferObjects\AdminData;
use Domain\Admin\Models\Admin;
use Filament\Facades\Filament;
use Filament\Forms;
use Filament\Pages\Actions\Action;
use Filament\Pages\Concerns\HasFormActions;
use Filament\Pages\Page;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

/**
 * @property \Filament\Forms\ComponentContainer $form
 */
class Account extends Page
{
//    use HasFormActions;
    use LogsFormActivity;

    protected static string $view = 'filament.pages.auth.account';

    protected static bool $shouldRegisterNavigation = false;

    public Admin $admin;

    public array $data;

    public function mount(): void
    {
        $this->admin = Filament::auth()->user() ?? abort(500);

        if ($this->admin->isZeroDayAdmin()) {
            abort(403);
        }

        $this->data = $this->admin->attributesToArray();

        $this->form->fill($this->data);

        $this->afterFill();
    }

    public function getBreadcrumbs(): array
    {
        return [
            url()->current() => $this->getTitle(),
            'Update',
        ];
    }

    public function getTitle(): string
    {
        return trans('My Account');
    }

    protected function getFormStatePath(): ?string
    {
        return 'data';
    }

    protected function getFormSchema(): array
    {
        return [
            Forms\Components\Card::make([
                Forms\Components\TextInput::make('first_name')
                    ->required()
                    ->string(),
                Forms\Components\TextInput::make('last_name')
                    ->required()
                    ->string(),
                Forms\Components\TextInput::make('email')
                    ->email()
                    ->required()
                    ->rules(Rule::email())
                    ->unique(Admin::class, ignorable: $this->admin)
                    ->helperText(! config('domain.admin.can_change_email') ? 'Email update is currently disabled.' : null)
                    ->disabled(! config('domain.admin.can_change_email'))
                    ->dehydrated((bool) config('domain.admin.can_change_email')),
                Forms\Components\TextInput::make('password')
                    ->password()
                    ->confirmed()
                    ->rules(Password::sometimes())
                    ->helperText(
                        app()->environment('local', 'testing')
                            ? trans('Password must be at least 4 characters.')
                            : trans('Password must be at least 8 characters, have 1 special character, 1 number, 1 upper case and 1 lower case.')
                    ),
                Forms\Components\TextInput::make('password_confirmation')
                    ->password()
                    ->nullable()
                    ->requiredWith('password')
                    ->same('password')
                    ->dehydrated(false),
                Forms\Components\Select::make('timezone')
                    ->options(
                        collect(timezone_identifiers_list())
                            ->mapWithKeys(fn (string $timezone) => [$timezone => $timezone])
                            ->toArray()
                    )
                    ->rules(['nullable', 'timezone'])
                    ->searchable(),
            ]),
        ];
    }

    public function save(): void
    {
        $this->admin = DB::transaction(fn () => app(UpdateAdminAction::class)
            ->execute($this->admin, new AdminData(...$this->form->getState())));

        if ($this->admin->wasChanged('password')) {
            session()->forget('password_hash_'.config('filament.auth.guard'));
            Filament::auth()->login($this->admin);
        }

        $this->afterSave();

        $this->notify('success', trans('Saved'));

        $this->redirect(self::getUrl());
    }

    protected function getFormActions(): array
    {
        return [
            Action::make('save')
                ->label(trans('Save changes'))
                ->submit('save')
                ->keyBindings(['mod+s']),
        ];
    }

    protected function shouldLogInitialState(): bool
    {
        return true;
    }

    protected function logsPerformedOn(): ?Model
    {
        return $this->admin;
    }
}
