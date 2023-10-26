<?php

declare(strict_types=1);

namespace App\FilamentTenant\Resources;

use App\FilamentTenant\Resources\ServiceBillResource\Pages\ListServiceBill;
use App\FilamentTenant\Resources\ServiceBillResource\Pages\ViewServiceBill;
use Artificertech\FilamentMultiContext\Concerns\ContextualResource;
use Domain\ServiceOrder\Enums\ServiceTransactionStatus;
use Domain\ServiceOrder\Models\ServiceBill;
use Filament\Resources\Form;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables;

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
                        return $record->serviceOrder->currency_symbol . ' ' . number_format((float) $record->total_amount, 2, '.', ',');
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
                        if( ! isset($record->due_date)) {
                            return 'Initial Payment';
                        }

                        return $record->due_date;
                    })
                    ->label('Due at')
                    ->sortable(),
            ])
        // ->actions([
        //     Tables\Actions\Action::make('print')
        //         ->translateLabel()
        //         // ->requiresConfirmation()
        //         ->button()
        //         ->icon('heroicon-o-download')
        //         ->action(function (ServiceTransaction $record, Tables\Actions\Action $action) {
        //             try {
        //                 /** @var \Illuminate\Support\Carbon $createdAt */
        //                 $createdAt = $record->created_at;

        //                 /** @var \Domain\Customer\Models\Customer $customer */
        //                 $customer = $record->serviceOrder->customer;

        //                 $filename =
        //                     $record->getKey().'-'.
        //                     $record->serviceOrder
        //                         ->getKey().
        //                     $customer
        //                         ->getKey().DIRECTORY_SEPARATOR.
        //                     Str::snake(app(SiteSettings::class)->name).
        //                     '_'.
        //                     $createdAt->format('m_Y').
        //                     '.pdf';

        //                 Pdf::loadView(
        //                     'web.layouts.service-order.receipts.default',
        //                     ['transaction' => $record]
        //                 )
        //                     ->save($filename, 'receipt-files');

        //                 $customer
        //                     ->addMedia(Storage::disk('receipt-files')->path($filename))
        //                     ->toMediaCollection('receipts');

        //                 $action
        //                     ->successNotificationTitle(trans('Success'))
        //                     ->success();

        //             } catch (Exception $e) {
        //                 $action
        //                     ->failureNotificationTitle($e->getMessage())
        //                     // ->failureNotificationTitle(trans('Something went wrong.'))
        //                     ->failure();

        //                 report($e);
        //             }
        //         })
        //         ->withActivityLog()
        //         ->authorize('customerPrintReceipt'),
        // ])
            ->bulkActions([])
            ->defaultSort('updated_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => ListServiceBill::route('/'),
            'view' => ViewServiceBill::route('/{record}'),
        ];
    }
}
