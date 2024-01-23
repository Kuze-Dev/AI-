<?php

declare(strict_types=1);

namespace App\FilamentTenant\Resources;

use App\FilamentTenant\Resources\ServiceBillResource\Pages\ViewServiceBill;
use Artificertech\FilamentMultiContext\Concerns\ContextualResource;
use Closure;
use Domain\ServiceOrder\Actions\ComputeServiceBillingCycleAction;
use Domain\ServiceOrder\Enums\ServiceOrderStatus;
use Domain\ServiceOrder\Enums\ServiceTransactionStatus;
use Domain\ServiceOrder\Models\ServiceBill;
use Domain\ServiceOrder\Models\ServiceOrder;
use Filament\Facades\Filament;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Resources\Resource;
use Filament\Tables\Table;
use Filament\Tables;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

class ServiceBillResource extends Resource
{
    use ContextualResource;

    protected static ?string $model = ServiceBill::class;

    protected static bool $shouldRegisterNavigation = false;

    protected static ?string $slug = 'service-bills';

    public static function getRouteBaseName(?string $panel = null): string
    {
        return Filament::currentContext().'.resources.service-orders.service-bills';
    }

    public static function getRoutes(): Closure
    {
        return function () {
            $slug = static::getSlug();

            Route::name("service-orders.{$slug}.")
                ->prefix('service-orders/{ownerRecord}')
                ->middleware(static::getMiddlewares())
                ->group(function () {
                    foreach (static::getPages() as $name => $page) {
                        Route::get($page['route'], $page['class'])->name($name);
                    }
                });
        };
    }

    public static function form(Form $form): Form
    {
        return $form
            ->columns(3)
            ->schema([]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('reference')
                    ->label('reference')
                    ->translateLabel()
                    ->sortable(),
                Tables\Columns\TextColumn::make('total_amount')
                    ->formatStateUsing(
                        function (ServiceBill $record): string {
                            /** @var \Domain\ServiceOrder\Models\ServiceOrder $serviceOrder */
                            $serviceOrder = $record->serviceOrder;

                            return $serviceOrder->currency_symbol.' '.
                                number_format((float) $record->total_amount, 2, '.', ',');
                        }
                    )
                    ->label('Amount')
                    ->translateLabel()
                    ->sortable(),
                Tables\Columns\TextColumn::make('total_balance')
                    ->formatStateUsing(
                        function (ServiceBill $record): string {
                            /** @var \Domain\ServiceOrder\Models\ServiceOrder $serviceOrder */
                            $serviceOrder = $record->serviceOrder;

                            return $serviceOrder->currency_symbol.' '.
                                number_format((float) $record->total_balance, 2, '.', ',');
                        }
                    )
                    ->label('Balance')
                    ->translateLabel()
                    ->sortable(),
                Tables\Columns\BadgeColumn::make('status')
                    ->label('Status')
                    ->translateLabel()
                    ->formatStateUsing(
                        fn (string $state): string => ucfirst(str_replace('_', ' ', strtolower($state)))
                    )
                    ->color(
                        fn (ServiceBill $record): string => $record->getStatusColor()
                    )
                    ->inline(),
                Tables\Columns\TextColumn::make('bill_date')
                    ->formatStateUsing(
                        fn (ServiceBill $record) => ! isset($record->bill_date)
                            ? 'N/A'
                            : $record->bill_date
                    )
                    ->label('Bill Date')
                    ->dateTime(timezone: Auth::user()?->timezone)
                    ->translateLabel()
                    ->sortable(),
                Tables\Columns\TextColumn::make('due_date')
                    ->formatStateUsing(
                        fn (ServiceBill $record) => ! isset($record->due_date)
                            ? 'N/A'
                            : $record->due_date
                    )
                    ->dateTime(timezone: Auth::user()?->timezone)
                    ->label('Due at')
                    ->translateLabel()
                    ->sortable(),
            ])
            ->headerActions([
                Tables\Actions\Action::make('view')
                    ->label(
                        function (RelationManager $livewire) {
                            /** @var \Domain\ServiceOrder\Models\ServiceOrder $serviceOrder */
                            $serviceOrder = $livewire->ownerRecord;

                            return self::upcomingBill($serviceOrder);
                        }
                    )
                    ->translateLabel()
                    ->color('gray')
                    ->disabled()
                    ->visible(
                        function (RelationManager $livewire) {
                            /** @var \Domain\ServiceOrder\Models\ServiceOrder $serviceOrder */
                            $serviceOrder = $livewire->ownerRecord;

                            return self::shouldDisplayUpcomingBill($serviceOrder);
                        }
                    ),
            ])
            ->bulkActions([])
            ->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'view' => ViewServiceBill::route('service-bills/{record}'),
        ];
    }

    private static function shouldDisplayUpcomingBill(ServiceOrder $serviceOrder): bool
    {
        $isAutoBilling = $serviceOrder->is_auto_generated_bill;

        $latestServiceBill = $serviceOrder->latestServiceBill();

        if ($isAutoBilling && isset($latestServiceBill) && $serviceOrder->status == ServiceOrderStatus::ACTIVE) {
            return true;
        }

        return false;
    }

    private static function upcomingBill(ServiceOrder $serviceOrder): string
    {
        /** @var \Domain\ServiceOrder\Models\ServiceBill|null $latestServiceBill */
        $latestServiceBill = $serviceOrder->latestServiceBill();

        /** @var \Illuminate\Support\Carbon|null $referenceDate */
        $referenceDate = $latestServiceBill?->bill_date;

        /** @var \Domain\ServiceOrder\Models\ServiceTransaction|null $serviceTransaction */
        $serviceTransaction = $latestServiceBill?->serviceTransactions()
            ->latest()
            ->whereStatus(ServiceTransactionStatus::PAID)
            ->first();

        if (is_null($referenceDate) && $serviceTransaction) {
            /** @var \Domain\ServiceOrder\DataTransferObjects\ServiceOrderBillingAndDueDateData
             *  $serviceOrderBillingAndDueDateData
             */
            $serviceOrderBillingAndDueDateData = app(ComputeServiceBillingCycleAction::class)
                ->execute(
                    $serviceOrder,
                    /** @phpstan-ignore-next-line */
                    $serviceTransaction->created_at
                );

            $referenceDate = $serviceOrderBillingAndDueDateData->bill_date;
        }

        $formattedState = Carbon::parse($referenceDate)
            ->format('F d, Y');

        return 'Upcoming Bill: '.$formattedState;
    }
}
