<?php

declare(strict_types=1);

namespace App\FilamentTenant\Resources\InviteCustomerResource\Pages;

use App\FilamentTenant\Resources\CustomerResource\Pages\ListCustomers;
use App\FilamentTenant\Resources\InviteCustomerResource;
use Domain\Customer\Actions\SendRegisterInvitationsAction;
use Domain\Customer\Enums\RegisterStatus;
use Domain\Customer\Imports\CustomerImporter;
use Filament\Actions;
// use Filament\Actions\ImportAction;
use Filament\Forms;
use Illuminate\Support\Str;

class ListInviteCustomers extends ListCustomers
{
    protected static string $resource = InviteCustomerResource::class;

    /** @throws \Exception */
    #[\Override]
    protected function getHeaderActions(): array
    {
        return [
            \Filament\Actions\ImportAction::make()
                ->color('primary')
                ->icon('heroicon-o-arrow-up-tray')
                ->label(trans('Import customer invite'))
                ->importer(CustomerImporter::class)
                ->withActivityLog(
                    event: 'imported',
                    description: fn (\Filament\Actions\ImportAction $action) => 'Imported '.$action->getModelLabel(),
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
