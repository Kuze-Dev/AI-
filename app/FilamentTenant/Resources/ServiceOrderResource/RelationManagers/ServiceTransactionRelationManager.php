<?php

declare(strict_types=1);

namespace App\FilamentTenant\Resources\ServiceOrderResource\RelationManagers;

use App\Settings\SiteSettings;
use Barryvdh\DomPDF\Facade\Pdf;
use Domain\ServiceOrder\Enums\ServiceTransactionStatus;
use Domain\ServiceOrder\Models\ServiceTransaction;
use Exception;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Resources\Table;
use Filament\Tables;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ServiceTransactionRelationManager extends RelationManager
{
    protected static string $relationship = 'serviceTransactions';

    protected static ?string $title = 'Payment';

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('serviceBill.reference')->exists('serviceBill')
                    ->label('reference')
                    ->sortable(),
                Tables\Columns\TextColumn::make('total_amount')->exists('serviceBill')
                    ->formatStateUsing(function (ServiceTransaction $record) {
                        return $record->currency.' '.number_format((float) $record->total_amount, 2, '.', ',');
                    })
                    ->label('Amount')
                    ->sortable(),
                Tables\Columns\TextColumn::make('payment_method.title')->exists('payment_method')
                    ->label('Payment Method')
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
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Updated at')
                    ->sortable(),
            ])
            ->actions([
                Tables\Actions\Action::make('print')
                    ->translateLabel()
                    ->requiresConfirmation()
                    ->button()
                    ->icon('heroicon-o-download')
                    ->action(function (ServiceTransaction $record, Tables\Actions\Action $action) {
                        try {
                            /** @var \Illuminate\Support\Carbon $createdAt */
                            $createdAt = $record->created_at;

                            /** @var \Domain\Customer\Models\Customer $customer */
                            $customer = $record->serviceOrder->customer;

                            /** @var string $filename */
                            $filename =
                                $record->getKey().'-'.
                                $record->serviceOrder
                                    ->getKey().
                                $customer
                                    ->getKey().DIRECTORY_SEPARATOR.
                                Str::snake(app(SiteSettings::class)->name).
                                '_'.
                                $createdAt->format('m_Y').
                                '.pdf';

                            Pdf::loadView(
                                'web.layouts.service-order.receipts.default',
                                ['transaction' => $record]
                            )
                                ->save($filename, 'receipt-files');

                            $customer
                                ->addMedia(
                                    Storage::disk('receipt-files')
                                        ->path($filename)
                                )
                                ->toMediaCollection('receipts');

                            $action
                                ->successNotificationTitle(trans('Success'))
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
                    })
                    ->withActivityLog()
                    ->authorize('customerPrintReceipt'),
            ])
            ->bulkActions([])
            ->defaultSort('updated_at', 'desc');
    }
}
