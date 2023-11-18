<?php

declare(strict_types=1);

namespace App\FilamentTenant\Pages;

use App\Features\Customer\TierBase;
use Artificertech\FilamentMultiContext\Concerns\ContextualPage;
use Domain\Customer\Actions\SendRegisterInvitationAction;
use Domain\Customer\Enums\RegisterStatus;
use Domain\Customer\Enums\Status;
use Domain\Customer\Models\Customer;
use Filament\Facades\Filament;
use Filament\Pages\Page;
use Filament\Tables\Actions\BulkAction;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\SpatieMediaLibraryImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

class InviteCustomers extends Page implements HasTable
{
    use ContextualPage;
    use InteractsWithTable;

    protected static ?string $navigationIcon = 'heroicon-o-speakerphone';

    protected static string $view = 'filament.pages.invite-customers';

    protected static ?string $navigationGroup = 'Customer Management';

    /** @return Builder<\Domain\Customer\Models\Customer> */
    protected function getTableQuery(): Builder
    {
        return Customer::query()
            ->where('status', '=', Status::INACTIVE)
            ->where('register_status', '=', RegisterStatus::UNREGISTERED)
            ->orWhere('register_status', '=', RegisterStatus::INVITED)
            ->latest();

    }

    protected function getTableColumns(): array
    {
        return [
            SpatieMediaLibraryImageColumn::make('image')
                ->collection('image')
                ->conversion('original')
                ->circular()
                ->toggleable(isToggledHiddenByDefault: true),

            TextColumn::make('full_name')
                ->translateLabel()
                ->searchable(['first_name', 'last_name'])
                ->sortable(['first_name', 'last_name'])
                ->wrap(),
            TextColumn::make('email')
                ->translateLabel()
                ->sortable(),
            IconColumn::make('email_verified_at')
                ->label(trans('Verified'))
                ->getStateUsing(fn (Customer $record): bool => $record->hasVerifiedEmail())
                ->boolean(),
            TextColumn::make('mobile')
                ->translateLabel()
                ->searchable()
                ->sortable()
                ->wrap(),
            BadgeColumn::make('tier.name')
                ->translateLabel()
                ->sortable()
                ->hidden(fn () => ! tenancy()->tenant?->features()->active(TierBase::class) ? true : false)
                ->toggleable(fn () => ! tenancy()->tenant?->features()->active(TierBase::class) ? false : true, isToggledHiddenByDefault: true)
                ->wrap(),
            BadgeColumn::make('status')
                ->translateLabel()
                ->sortable()
                ->colors([
                    'success' => Status::ACTIVE->value,
                    'warning' => Status::INACTIVE->value,
                    'danger' => Status::BANNED->value,
                ]),
            BadgeColumn::make('register_status')
                ->translateLabel()
                ->sortable()
                ->toggleable(isToggledHiddenByDefault: true)
                ->colors([
                    'success' => RegisterStatus::REGISTERED->value,
                    'warning' => RegisterStatus::INVITED->value,
                    'danger' => RegisterStatus::UNREGISTERED->value,
                ]),
            TextColumn::make('updated_at')
                ->translateLabel()
                ->dateTime(timezone: Filament::auth()->user()?->timezone)
                ->sortable(),

        ];
    }

    public function getTableBulkActions()
    {
        return [
            BulkAction::make('invite')
                ->translateLabel()
                ->action(function (Collection $records, BulkAction $action) {

                    $success = null;
                    /** @var \Domain\Customer\Models\Customer $customer */
                    foreach ($records as $customer) {
                        $success = app(SendRegisterInvitationAction::class)->execute($customer);
                    }
                    if ($success) {
                        $action
                            ->successNotificationTitle(trans('Invitation Email sent.'))
                            ->success();
                    } else {
                        $action->failureNotificationTitle(trans('Failed to send  invitation. Invite inactive and unregistered users only'))
                            ->failure();
                    }
                })
                ->deselectRecordsAfterCompletion()
                ->icon('heroicon-o-speakerphone'),
        ];
    }
}
