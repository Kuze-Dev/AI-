<?php

declare(strict_types=1);

namespace App\FilamentTenant\Resources;

use App\FilamentTenant\Resources\ServiceBillResource\Pages\ListServiceBill;
use App\FilamentTenant\Resources\ServiceBillResource\Pages\ViewServiceBill;
use Artificertech\FilamentMultiContext\Concerns\ContextualResource;
use Carbon\Carbon;
use Domain\ServiceOrder\Actions\ComputeServiceBillingCycle;
use Domain\ServiceOrder\Enums\ServiceTransactionStatus;
use Domain\ServiceOrder\Models\ServiceBill;
use Filament\Resources\Form;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables;
use Illuminate\Support\Facades\Auth;

class ServiceBillResource extends Resource
{
    use ContextualResource;

    protected static ?string $model = ServiceBill::class;

    protected static bool $shouldRegisterNavigation = false;

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
                    ->sortable(),
                Tables\Columns\TextColumn::make('total_amount')
                    ->formatStateUsing(function (ServiceBill $record) {
                        return $record->serviceOrder->currency_symbol.' '.number_format((float) $record->total_amount, 2, '.', ',');
                    })
                    ->label('Amount')
                    ->sortable(),
                Tables\Columns\BadgeColumn::make('status')
                    ->label(trans('Status'))
                    ->alignRight()
                    ->formatStateUsing(function (string $state): string {
                        return ucfirst($state);
                    })
                    ->color(function ($state) {
                        $newState = str_replace(' ', '_', strtolower($state));

                        return match ($newState) {
                            ServiceTransactionStatus::PAID->value => 'success',
                            ServiceTransactionStatus::PENDING->value => 'warning',
                            ServiceTransactionStatus::REFUNDED->value => 'danger',
                            default => 'secondary',
                        };
                    })->inline()
                    ->alignLeft(),
                Tables\Columns\TextColumn::make('due_date')
                    ->formatStateUsing(function (ServiceBill $record) {
                        if (! isset($record->due_date)) {
                            return 'N/A';
                        }

                        return $record->due_date;
                    })
                    ->label('Due at')
                    ->sortable(),
                Tables\Columns\TextColumn::make('bill_date')
                    ->formatStateUsing(function (ServiceBill $record) {
                        if (! isset($record->due_date)) {
                            return 'N/A';
                        }

                        return $record->due_date;
                    })
                    ->label('Bill Date')
                    ->sortable(),
            ])
            ->headerActions([
                Tables\Actions\Action::make('view')
                    ->label(function ($livewire) {
                        $serviceOrder = $livewire->ownerRecord;

                        /** @var \Domain\ServiceOrder\Models\ServiceBill $latestServiceBill */
                        $latestServiceBill = $serviceOrder->latestServiceBill();

                        /** @var \Carbon\Carbon|null $referenceDate */
                        $referenceDate = $latestServiceBill->bill_date;

                        /** @var \Domain\ServiceOrder\Models\ServiceTransaction|null $serviceTransaction */
                        $serviceTransaction = $latestServiceBill->serviceTransaction;

                        if (is_null($referenceDate) && $serviceTransaction) {
                            /** @var \Domain\ServiceOrder\DataTransferObjects\ServiceOrderBillingAndDueDateData
                             *  $serviceOrderBillingAndDueDateData
                             */
                            $serviceOrderBillingAndDueDateData = app(ComputeServiceBillingCycle::class)
                                ->execute(
                                    $serviceOrder,
                                    /** @phpstan-ignore-next-line */
                                    $serviceTransaction->created_at
                                );

                            $referenceDate = $serviceOrderBillingAndDueDateData->bill_date;
                        }

                        /** @var string */
                        $timeZone = Auth::user()?->timezone;

                        $formattedState = Carbon::parse($referenceDate)
                            ->setTimezone($timeZone)
                            ->translatedFormat('F d, Y g:i A');

                        return 'Upcoming Bill: '.$formattedState;
                    })
                    ->translateLabel()
                    ->color('secondary')
                    ->disabled()->visible(
                        function ($livewire) {
                            $isAutoBilling = $livewire->ownerRecord->is_auto_generated_bill;

                            return $isAutoBilling;
                        }
                    ),
            ])
            ->bulkActions([])
            ->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => ListServiceBill::route('/'),
            'view' => ViewServiceBill::route('/{record}'),
        ];
    }
}
