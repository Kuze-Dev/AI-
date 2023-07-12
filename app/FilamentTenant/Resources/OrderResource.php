<?php

declare(strict_types=1);

namespace App\FilamentTenant\Resources;

use Artificertech\FilamentMultiContext\Concerns\ContextualResource;
use Domain\Order\Models\Order;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables;
use Filament\Forms;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;
use Carbon\Carbon;

class OrderResource extends Resource
{
    use ContextualResource;

    protected static ?string $navigationGroup = 'eCommerce';

    protected static ?string $model = Order::class;

    protected static ?string $navigationIcon = 'heroicon-o-collection';

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('reference')->label("Order ID")->sortable()->searchable(),
                Tables\Columns\TextColumn::make('customer')->label("Customer")
                    ->sortable()
                    ->formatStateUsing(function ($record) {
                        return $record->customer->fullName;
                    }),
                Tables\Columns\TextColumn::make('tax_total')->label("Tax Total")->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('total')->sortable(),
                Tables\Columns\TextColumn::make('shipping_method')->label("Shipping Method")->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('payment_method')->label("Payment Method")->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\IconColumn::make('is_paid')->label("isPaid")
                    ->boolean()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('created_at')
                    ->sortable()
                    ->label("Order Date")
                    ->dateTime(timezone: Auth::user()?->timezone),
                Tables\Columns\BadgeColumn::make('status')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\Filter::make('created_at')
                    ->form([
                        Forms\Components\DatePicker::make('created_from')
                            ->label('Created from'),
                        Forms\Components\DatePicker::make('created_until')
                            ->label('Created until'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['created_from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '>=', $date),
                            )
                            ->when(
                                $data['created_until'],
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '<=', $date),
                            );
                    })
                    ->indicateUsing(function (array $data): array {
                        $indicators = [];

                        if ($data['created_from'] ?? null) {
                            $indicators['created_from'] = 'Created from ' . Carbon::parse($data['created_from'])->toFormattedDateString();
                        }

                        if ($data['created_until'] ?? null) {
                            $indicators['created_until'] = 'Created until ' . Carbon::parse($data['created_until'])->toFormattedDateString();
                        }

                        return $indicators;
                    }),
            ])
            ->bulkActions([
                // Tables\Actions\BulkAction::make('export')
                //     ->action(function (Collection $records) {
                //         return Excel::download(new ExportCollection($records), 'orders.csv');
                //     })
                //     ->color('primary')
                //     ->icon('heroicon-o-check')
            ])
            ->actions([
                Tables\Actions\ViewAction::make()->color("primary"),
            ])
            ->defaultSort('id', 'DESC');
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function getPages(): array
    {
        return [
            'index' => OrderResource\Pages\ListOrders::route('/'),
        ];
    }
}
