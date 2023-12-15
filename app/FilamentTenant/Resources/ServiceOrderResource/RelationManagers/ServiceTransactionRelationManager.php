<?php

declare(strict_types=1);

namespace App\FilamentTenant\Resources\ServiceOrderResource\RelationManagers;

use Domain\ServiceOrder\Actions\GenerateServiceTransactionReceiptAction;
use Domain\ServiceOrder\Models\ServiceTransaction;
use Exception;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Resources\Table;
use Filament\Tables;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;

class ServiceTransactionRelationManager extends RelationManager
{
    protected static string $relationship = 'serviceTransactions';

    protected static ?string $title = 'Payment';

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('payment.payment_id')
                    ->exists('payment')
                    ->label(trans('Payment ID'))
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
                    ->dateTime(timezone: Auth::user()?->timezone)
                    ->sortable(),
                Tables\Columns\TextColumn::make('updated_at')
                    ->translateLabel()
                    ->dateTime(timezone: Auth::user()?->timezone)
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
                                $pdf = app(GenerateServiceTransactionReceiptAction::class)
                                    ->execute($record);

                                Redirect::away($pdf->original_url);

                            } catch (ModelNotFoundException $m) {
                                $action
                                    ->failureNotificationTitle(trans('Unable to generate receipt'))
                                    ->failure();

                                report($m);
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
