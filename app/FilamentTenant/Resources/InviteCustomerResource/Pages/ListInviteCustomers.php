<?php

declare(strict_types=1);

namespace App\FilamentTenant\Resources\InviteCustomerResource\Pages;

use App\FilamentTenant\Resources\CustomerResource\Pages\ListCustomers;
use App\FilamentTenant\Resources\InviteCustomerResource;
use App\Settings\CustomerSettings;
use Domain\Customer\Actions\ImportCustomerAction;
use Domain\Customer\Actions\SendRegisterInvitationsAction;
use Domain\Customer\Enums\Gender;
use Domain\Customer\Enums\RegisterStatus;
use Domain\Customer\Models\Customer;
use Domain\Tier\Models\Tier;
use Filament\Actions;
// use Filament\Actions\ImportAction;
use HalcyonAgile\FilamentImport\Actions\ImportAction;
use Filament\Forms;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class ListInviteCustomers extends ListCustomers
{
    protected static string $resource = InviteCustomerResource::class;

    /** @throws \Exception */
    #[\Override]
    protected function getHeaderActions(): array
    {
        $date_format = app(CustomerSettings::class)->date_format;

        return [
            ImportAction::make()
                ->model(Customer::class)
                ->uniqueBy('email')
//                ->batchSize(100)
//                ->chunkSize(100)
                ->tags([
                    'tenant:'.(tenant('id') ?? 'central'),
                ])
                ->processRowsUsing(
                    fn (array $row) => app(ImportCustomerAction::class)
                        ->execute(array_filter($row))
                )
                ->withValidation(
                    rules: [
                        'email' => [
                            'required',
                            Rule::email(),
                            'distinct',
                        ],
                        'username' => 'nullable',
                        'first_name' => 'nullable|string|min:1|max:100',
                        'last_name' => 'nullable|string|min:1|max:100',
                        'mobile' => 'nullable|min:3|max:100',
                        'gender' => ['nullable', Rule::enum(Gender::class)],
                        'birth_date' => [
                            'nullable',
                            ($date_format == 'default' ||
                            $date_format == '') ? 'date' : 'date_format:'.$date_format],
                        'password' => 'nullable',
                        'registered' => 'nullable',
                        'data' => 'nullable',
                        'tier' => [
                            'nullable',
                            Rule::exists(Tier::class, 'name'),
                        ],
                    ],
                ),
            Actions\Action::make('send-register-invitation')
                ->translateLabel()
                ->icon('heroicon-o-megaphone')
                ->form(fn () => [
                    Forms\Components\CheckboxList::make('register_status')
                        ->translateLabel()
                        ->required()
                        ->options(
                            collect(RegisterStatus::allowedResendInviteCases())
                                ->mapWithKeys(fn (RegisterStatus $status) => [
                                    $status->value => Str::headline($status->value),
                                ])
                        ),
                ])
                ->successNotificationTitle(
                    fn () => trans('A registration link has been sending to all email address.')
                )
                ->action(function (Actions\Action $action, array $data) {
                    app(SendRegisterInvitationsAction::class)
                        ->execute(registerStatuses: $data['register_status']);

                    $action->success();
                }),
            Actions\CreateAction::make(),
        ];
    }
}
