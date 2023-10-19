<?php

declare(strict_types=1);

namespace App\FilamentTenant\Resources\ServiceOrderResource\RelationManagers;

use App\Settings\SiteSettings;
use Domain\ServiceOrder\Enums\ServiceTransactionStatus;
use Domain\ServiceOrder\Models\ServiceTransaction;
use Exception;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Resources\Table;
use Filament\Tables;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Spatie\Browsershot\Browsershot;

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
                        return $record->currency . ' ' . number_format((float) $record->total_amount, 2, '.', ',');
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
                            $createdAt = $record->created_at->format('m_Y');

                            $filename = Str::snake(app(SiteSettings::class)->name).
                                '_'.
                                $createdAt.
                                '.pdf';

                            $path = Storage::disk('receipt-files')->path($filename);

                            try {
                                /**
                                 * Set .env values for
                                 * BROWSERSHOT_NODE_PATH and BROWSERSHOT_NPM_PATH
                                 * Docs: https://spatie.be/docs/browsershot/v2/requirements
                                 */
                                Browsershot::html(
                                    view(
                                        'web.layouts.service-order.receipts.default',
                                        ['transaction' => $record]
                                    )
                                        ->render()
                                )
                                    ->setNodeBinary(config('browsershot.node_path'))
                                    ->setNpmBinary(config('browsershot.npm_path'))
                                    ->save($path);
                            } catch (Exception $e) {
                                report($e);
                            }

                            $record->serviceOrder
                                ->customer
                                ->addMedia($path)
                                ->toMediaCollection('receipts');

                            $action
                                ->successNotificationTitle(trans('Success'))
                                ->success();
                        } catch (Exception $e) {
                            $action
                                ->failureNotificationTitle($e->getMessage())
                                // ->failureNotificationTitle(trans('Something went wrong.'))
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
