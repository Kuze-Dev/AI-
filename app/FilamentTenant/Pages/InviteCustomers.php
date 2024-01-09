<?php

declare(strict_types=1);

namespace App\FilamentTenant\Pages;

use App\Features\Customer\CustomerBase;
use App\Features\Customer\TierBase;
use Artificertech\FilamentMultiContext\Concerns\ContextualPage;
use Domain\Customer\Actions\CreateCustomerAction;
use Domain\Customer\Actions\EditCustomerAction;
use Domain\Customer\Actions\SendRegisterInvitationAction;
use Domain\Customer\DataTransferObjects\CustomerData;
use Domain\Customer\Enums\Gender;
use Domain\Customer\Enums\RegisterStatus;
use Domain\Customer\Enums\Status;
use Domain\Customer\Exceptions\NoSenderEmailException;
use Domain\Customer\Models\Customer;
use Domain\Tier\Models\Tier;
use Filament\Facades\Filament;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Tables\Actions\BulkAction;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\SpatieMediaLibraryImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use HalcyonAgile\FilamentImport\Actions\ImportAction;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;

class InviteCustomers extends Page implements HasTable
{
    use ContextualPage;
    use InteractsWithTable;

    protected static ?string $navigationIcon = 'heroicon-o-speakerphone';

    protected static string $view = 'filament.pages.invite-customers';

    protected static ?string $navigationGroup = 'Customer Management';

    public static function registerNavigationItems(): void
    {
        if (! tenancy()->tenant?->features()->active(CustomerBase::class)) {
            return;
        }
        Filament::registerNavigationItems(static::getNavigationItems());

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
                ->searchable()
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

    public function getTableBulkActions(): array
    {
        return [
            BulkAction::make('invite')
                ->translateLabel()
                ->action(function (Collection $records, BulkAction $action) {
                    try {
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
                    } catch (NoSenderEmailException $s) {
                        return Notification::make()
                            ->danger()
                            ->title(trans($s->getMessage()))
                            ->send();
                    }

                })
                ->deselectRecordsAfterCompletion()
                ->icon('heroicon-o-speakerphone'),
        ];
    }

    protected function getTableFilters(): array
    {
        return [
            TrashedFilter::make()
                ->translateLabel(),
            SelectFilter::make('tier')
                ->hidden(fn () => ! tenancy()->tenant?->features()->active(TierBase::class) ? true : false)
                ->translateLabel()
                ->relationship('tier', 'name'),
            SelectFilter::make('status')
                ->translateLabel()
                ->options([
                    'active' => ucfirst(Status::ACTIVE->value),
                    'inactive' => ucfirst(Status::INACTIVE->value),
                ]),
            SelectFilter::make('email_verified')
                ->translateLabel()
                ->options(['1' => 'Verified', '0' => 'Not Verified'])
                ->query(function (Builder $query, array $data) {
                    $query->when(filled($data['value']), function (Builder $query) use ($data) {
                        /** @var \Domain\Customer\Models\Customer|\Illuminate\Database\Eloquent\Builder $query */
                        match ($data['value']) {
                            '1' => $query->whereNotNull('email_verified_at'),
                            '0' => $query->whereNull('email_verified_at'),
                            default => '',
                        };
                    });
                }),
        ];
    }

    /**
     * @throws \Exception
     */
    protected function getActions(): array
    {
        return [
            ImportAction::make()
                ->model(Customer::class)
                ->uniqueBy('email')
                ->tags([
                    'tenant:'.(tenant('id') ?? 'central'),
                ])
                ->processRowsUsing(
                    function (array $row): Customer {
                        $data = [
                            'email' => $row['email'],
                            'first_name' => $row['first_name'] ?? '',
                            'last_name' => $row['last_name'] ?? '',
                            'mobile' => $row['mobile'] ? (string) $row['mobile'] : null,
                            'gender' => $row['gender'] ?? null,
                            'status' => $row['status'] ?? null,
                            'birth_date' => is_null($row['birth_date']) ? null : Carbon::createFromFormat('Y/m/d', $row['birth_date']),
                            'tier_id' => isset($row['tier'])
                                ? (Tier::whereName($row['tier'])->first()?->getKey())
                                : null,
                        ];
                        unset($row);

                        $customer = Customer::whereEmail($data['email'])->first();

                        if ($customer?->register_status === RegisterStatus::UNREGISTERED) {
                            return app(EditCustomerAction::class)
                                ->execute($customer, CustomerData::fromArrayImportByAdmin($data));
                        }

                        return app(CreateCustomerAction::class)
                            ->execute(CustomerData::fromArrayImportByAdmin($data));

                    }
                )
                ->withValidation(
                    rules: [
                        'email' => [
                            'required',
                            Rule::email(),
                            'distinct',
                        ],
                        'first_name' => 'nullable|string|min:3|max:100',
                        'last_name' => 'nullable|string|min:3|max:100',
                        'mobile' => 'nullable|min:3|max:100',
                        'gender' => ['nullable', Rule::enum(Gender::class)],
                        'status' => ['nullable', Rule::enum(Status::class)],
                        'birth_date' => ['nullable', 'date_format:Y/m/d', 'before:today'],
                        'tier' => [
                            'nullable',
                            Rule::exists(Tier::class, 'name'),
                        ],
                    ],
                ),
        ];
    }

    /**
     * Paginate the table query.
     *
     * @param  \Illuminate\Database\Eloquent\Builder <\Domain\Customer\Models\Customer>  $query
     * @return \Illuminate\Contracts\Pagination\Paginator<\Domain\Customer\Models\Customer>
     */
    protected function paginateTableQuery(Builder $query): Paginator
    {
        return $query->paginate(10);
    }

    /** @return Builder<\Domain\Customer\Models\Customer> */
    protected function getTableQuery(): Builder
    {
        return Customer::query()
            ->where('register_status', '=', RegisterStatus::UNREGISTERED)
            ->orWhere('register_status', '=', RegisterStatus::INVITED)
            ->latest();

    }
}
