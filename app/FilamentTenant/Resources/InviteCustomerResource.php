<?php

declare(strict_types=1);

namespace App\FilamentTenant\Resources;

use Domain\Customer\Enums\RegisterStatus;
use Domain\Customer\Models\Customer;
use Exception;
use Filament\Resources\Table;
use Filament\Tables;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Str;

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

        return $table->filters(array_merge($table->getFilters(), [
            Tables\Filters\SelectFilter::make('register_status')
                ->translateLabel()
                ->options([
                    RegisterStatus::INVITED->value => Str::headline(RegisterStatus::INVITED->value),
                    RegisterStatus::UNREGISTERED->value => Str::headline(RegisterStatus::UNREGISTERED->value),
                ]),
        ]));
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
