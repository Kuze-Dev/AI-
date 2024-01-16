<?php

declare(strict_types=1);

namespace App\FilamentTenant\Resources;

use Domain\Customer\Actions\DeleteCustomerAction;
use Domain\Customer\Actions\ForceDeleteCustomerAction;
use Domain\Customer\Actions\RestoreCustomerAction;
use Domain\Customer\Actions\SendRegisterInvitationAction;
use Domain\Customer\Enums\RegisterStatus;
use Domain\Customer\Models\Customer;
use Domain\Tier\Enums\TierApprovalStatus;
use ErrorException;
use Exception;
use Filament\Resources\Table;
use Filament\Tables;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Support\ConstraintsRelationships\Exceptions\DeleteRestrictedException;

class InviteCustomerResource extends CustomerResource
{
    protected static ?string $navigationIcon = 'heroicon-o-speakerphone';

    protected static ?int $navigationSort = 1;

    protected static ?string $slug = 'invite-customers';

    public static function getModelLabel(): string
    {
        return trans('Invite Customer');
    }

    /** @throws Exception */
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
                        ->icon('heroicon-o-speakerphone')
                        ->action(function (Customer $record, Tables\Actions\Action $action): void {

                            $success = app(SendRegisterInvitationAction::class)
                                ->execute($record);

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
                        ->translateLabel()
                        ->using(function (Customer $record) {
                            try {
                                return app(DeleteCustomerAction::class)->execute($record);
                            } catch (DeleteRestrictedException) {
                                return false;
                            }
                        }),
                    Tables\Actions\RestoreAction::make()
                        ->translateLabel()
                        ->using(
                            fn (Customer $record) => DB::transaction(
                                fn () => app(RestoreCustomerAction::class)
                                    ->execute($record)
                            )
                        ),
                    Tables\Actions\ForceDeleteAction::make()
                        ->translateLabel()
                        ->using(function (Customer $record) {
                            try {
                                return app(ForceDeleteCustomerAction::class)->execute($record);
                            } catch (DeleteRestrictedException) {
                                return false;
                            }
                        }),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => InviteCustomerResource\Pages\ListInviteCustomers::route('/'),
            'create' => InviteCustomerResource\Pages\CreateInviteCustomer::route('/create'),
            'edit' => InviteCustomerResource\Pages\EditInviteCustomer::route('/{record}/edit'),
        ];
    }

    /** @return Builder<\Domain\Customer\Models\Customer> */
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

    public static function canCreate(): bool
    {
        return static::can('create');
    }
}
