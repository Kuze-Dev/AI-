<?php

declare(strict_types=1);

namespace App\FilamentTenant\Resources;

use Domain\Customer\Actions\SendRegisterInvitationAction;
use Domain\Customer\Actions\SendRegisterInvitationsAction;
use Domain\Customer\Enums\RegisterStatus;
use Domain\Customer\Exceptions\NoSenderEmailException;
use Domain\Customer\Models\Customer;
use Domain\Tier\Enums\TierApprovalStatus;
use ErrorException;
use Exception;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Str;

class InviteCustomerResource extends CustomerResource
{
    protected static ?string $navigationIcon = 'heroicon-o-megaphone';

    protected static ?int $navigationSort = 1;

    protected static ?string $slug = 'invite-customers';

    #[\Override]
    public static function getModelLabel(): string
    {
        return trans('Invite Customer');
    }

    /** @throws Exception */
    #[\Override]
    public static function table(Table $table): Table
    {
        $table = parent::table($table);

        return $table
            ->filters(array_merge($table->getFilters(), [
                Tables\Filters\SelectFilter::make('register_status')
                    ->translateLabel()
                    ->options([
                        RegisterStatus::INVITED->value => Str::headline(RegisterStatus::INVITED->value),
                        RegisterStatus::UNREGISTERED->value => Str::headline(RegisterStatus::UNREGISTERED->value),
                    ]),
            ]))
            ->actions([
                Tables\Actions\EditAction::make()
                    ->translateLabel()
                    ->hidden(fn (?Customer $record) => $record?->tier_approval_status === TierApprovalStatus::REJECTED),
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\Action::make('send-register-invitation')
                        ->label(fn (Customer $record) => match ($record->register_status) {
                            RegisterStatus::UNREGISTERED => 'Send register invitation',
                            RegisterStatus::INVITED => 'Resend register invitation',
                            default => throw new ErrorException('Invalid register status.'),
                        })
                        ->translateLabel()
                        ->requiresConfirmation()
                        ->icon('heroicon-o-megaphone')
                        ->action(function (Customer $record, Tables\Actions\Action $action): void {

                            try {
                                $success = app(SendRegisterInvitationAction::class)
                                    ->execute($record);
                            } catch (NoSenderEmailException $e) {
                                $action->failureNotificationTitle($e->getMessage())
                                    ->failure();

                                return;
                            }

                            if ($success) {
                                $action
                                    ->successNotificationTitle(trans('A registration link has been sent to your email address.'))
                                    ->success();

                                return;
                            }

                            $action->failureNotificationTitle(trans('Failed to send register invitation.'))
                                ->failure();

                        })
                        ->authorize('sendRegisterInvitation')
                        ->withActivityLog(
                            event: 'register-invitation-link-sent',
                            description: fn (Customer $record) => $record->full_name.' register invitation link sent'
                        ),
                    Tables\Actions\DeleteAction::make()
                        ->translateLabel(),
                    Tables\Actions\RestoreAction::make()
                        ->translateLabel(),
                    Tables\Actions\ForceDeleteAction::make()
                        ->translateLabel(),
                ]),
            ])
            ->bulkActions([
                Tables\Actions\BulkAction::make('send-register-invitation')
                    ->translateLabel()
                    ->icon('heroicon-o-megaphone')
                    ->requiresConfirmation()
                    ->deselectRecordsAfterCompletion()
                    ->successNotificationTitle(
                        fn () => trans('A registration link has been sending to selected email address.')
                    )
                    ->action(function (Tables\Actions\BulkAction $action, Collection $records) {
                        /** @var Collection<int, Customer> $records */
                        app(SendRegisterInvitationsAction::class)
                            ->execute(records: $records);

                        $action->success();
                    }),
                Tables\Actions\DeleteBulkAction::make()
                    ->authorize('delete'),
                Tables\Actions\ForceDeleteBulkAction::make()
                    ->authorize('forceDelete'),
                Tables\Actions\RestoreBulkAction::make()
                    ->authorize('restore'),
            ]);
    }

    #[\Override]
    public static function getPages(): array
    {
        return [
            'index' => InviteCustomerResource\Pages\ListInviteCustomers::route('/'),
            'create' => InviteCustomerResource\Pages\CreateInviteCustomer::route('/create'),
            'edit' => InviteCustomerResource\Pages\EditInviteCustomer::route('/{record}/edit'),
        ];
    }

    /** @return Builder<\Domain\Customer\Models\Customer> */
    #[\Override]
    public static function getEloquentQuery(): Builder
    {
        /** @var Builder<Customer> $query */
        $query = Customer::query();

        return $query
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ])
            ->whereNot('register_status', RegisterStatus::REGISTERED);
    }

    #[\Override]
    public static function canCreate(): bool
    {
        return static::can('create');
    }
}
