<?php

declare(strict_types=1);

namespace App\FilamentTenant\Resources\ServiceOrderResource\RelationManagers;

use App\Settings\SiteSettings;
use Barryvdh\DomPDF\Facade\Pdf;
use Domain\ServiceOrder\Models\ServiceTransaction;
use Exception;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Resources\Table;
use Filament\Tables;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Str;

class ServiceTransactionRelationManager extends RelationManager
{
    protected static string $relationship = 'serviceTransactions';

    protected static ?string $title = 'Payment';

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('serviceBill.reference')
                    ->exists('serviceBill')
                    ->label(trans('Reference'))
                    ->sortable(),
                Tables\Columns\TextColumn::make('total_amount')
                    ->exists('serviceBill')
                    ->formatStateUsing(
                        fn (ServiceTransaction $record): string => $record->getTotalAmountWithCurrency()
                    )
                    ->label(trans('Amount'))
                    ->sortable(),
                Tables\Columns\TextColumn::make('payment_method.title')
                    ->exists('payment_method')
                    ->label(trans('Payment Method'))
                    ->sortable(),
                Tables\Columns\BadgeColumn::make('status')
                    ->translateLabel()
                    ->formatStateUsing(
                        fn (string $state): string => ucfirst($state)
                    )
                    ->color(
                        fn (ServiceTransaction $record): string => $record->getStatusColor()
                    )
                    ->inline(),
                Tables\Columns\TextColumn::make('created_at')
                    ->translateLabel()
                    ->sortable(),
                Tables\Columns\TextColumn::make('updated_at')
                    ->translateLabel()
                    ->sortable(),
            ])
            ->actions([
                Tables\Actions\Action::make('print')
                    ->translateLabel()
                    ->requiresConfirmation()
                    ->button()
                    ->icon('heroicon-o-download')
                    ->action(
                        function (ServiceTransaction $record, Tables\Actions\Action $action) {
                            try {
                                /** @var \Illuminate\Support\Carbon $createdAt */
                                $createdAt = $record->created_at;

                                /** @var \Domain\ServiceOrder\Models\ServiceOrder $serviceOrder */
                                $serviceOrder = $record->serviceOrder;

                                /** @var \Domain\Customer\Models\Customer $customer */
                                $customer = $serviceOrder->customer;

                                /** @var string $filename */
                                $filename = $record->getKey().'-'.
                                    $serviceOrder->getKey().
                                    $customer->getKey().DIRECTORY_SEPARATOR.
                                    Str::snake(app(SiteSettings::class)->name).'_'.
                                    $createdAt->format('m_Y').
                                    '.pdf';

                                $disk = config('domain.service-order.disks.receipt-files.driver');

                                Pdf::loadView(
                                    'web.layouts.service-order.receipts.default',
                                    ['transaction' => $record]
                                )
                                    ->save($filename, $disk);

                                $customer->addMediaFromDisk($filename, $disk)
                                    ->toMediaCollection('receipts');

                                $action->successNotificationTitle(trans('Success'))
                                    ->success();

                                /** @var \Spatie\MediaLibrary\MediaCollections\Models\Media $pdf */
                                $pdf = $customer->getMedia('receipts')
                                    ->sortByDesc('id')
                                    ->first();

                                Redirect::away($pdf->original_url);

                            } catch (Exception $e) {
                                $action
                                    ->failureNotificationTitle(trans('Something went wrong!'))
                                    ->failure();

                                report($e);
                            }
                        }
                    )
                    ->withActivityLog()
                    ->visible(fn (ServiceTransaction $record) => $record->is_paid)
                    ->authorize('customerPrintReceipt'),
            ])
            ->bulkActions([])
            ->defaultSort('created_at', 'desc');
    }
}
