<?php

declare(strict_types=1);

namespace App\FilamentTenant\Resources\ServiceBillResource\Pages;

use App\FilamentTenant\Resources\ServiceBillResource;
use Carbon\Carbon;
use DateTimeZone;
use Domain\Admin\Models\Admin;
use Filament\Forms;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Section;
use Filament\Resources\Pages\ViewRecord;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Contracts\Support\Htmlable;
use App\FilamentTenant\Support;
use App\FilamentTenant\Support\BadgeLabel;
use App\FilamentTenant\Support\Divider;
use App\FilamentTenant\Support\TextLabel;
use Closure;
use Domain\ServiceOrder\Enums\ServiceOrderStatus;
use Domain\ServiceOrder\Models\ServiceBill;
use Domain\ServiceOrder\Models\ServiceOrder;
use Domain\Taxation\Enums\PriceDisplay;
use Illuminate\Support\Facades\Auth;

class ViewServiceBill extends ViewRecord
{
    protected static string $resource = ServiceBillResource::class;

    protected function getHeading(): string|Htmlable
    {
        return trans('Service Bill Details #') . $this->record->reference;
    }

    protected function getFormSchema(): array
    {
        $admin = Admin::first();
        return [
            Forms\Components\Group::make()
                ->schema([
                    Forms\Components\Group::make()->columnSpan(2)->schema([
                        Section::make(trans('Service'))->schema([
                            Grid::make(2)->schema([
                                Placeholder::make('service')
                                    ->content(fn ($record) => $record->serviceOrder->service_name),
                                Placeholder::make('service Price')
                                    ->content(fn ($record) => $record->serviceOrder->service_price),
                            ]),
                        ]),
                        Section::make(trans('Additional Charges'))
                            ->schema([
                                Forms\Components\Group::make()->schema($this->getAdditionalCharges()),
                            ])->visible(fn ($record) => !empty($record->additional_charges)),

                    ]),

                    Section::make(trans('Summary'))->columnSpan(1)->schema([
                        Forms\Components\Group::make()->columns(2)
                            ->schema([
                                TextLabel::make('')
                                ->label(trans('Status'))
                                ->alignLeft()
                                ->size('md')
                                ->inline()
                                ->readOnly(),
                                BadgeLabel::make(trans('status'))->formatStateUsing(function (string $state): string {
                                    if ($state == ServiceOrderStatus::FORPAYMENT->value) {
                                        return trans('For Payment');
                                    }
                                    if ($state == ServiceOrderStatus::INPROGRESS->value) {
                                        return trans('In Progress');
                                    }

                                    return ucfirst($state);
                                })
                                    ->color(function ($state) {
                                        $newState = str_replace(' ', '_', strtolower($state));

                                        return match ($newState) {
                                            ServiceOrderStatus::PENDING->value, ServiceOrderStatus::INPROGRESS->value => 'warning',
                                            ServiceOrderStatus::CLOSED->value, ServiceOrderStatus::INACTIVE->value, ServiceOrderStatus::CLOSED->value => 'danger',
                                            ServiceOrderStatus::COMPLETED->value, ServiceOrderStatus::ACTIVE->value => 'success',
                                            default => 'secondary',
                                        };
                                    })->inline()
                                    ->alignRight(),
                            ]),
                        Divider::make(''),
                        Forms\Components\Group::make()->columns(2)->schema([
                            TextLabel::make('')
                                ->label(trans('Service Price'))
                                ->alignLeft()
                                ->size('md')
                                ->inline()
                                ->readOnly(),
                            TextLabel::make('')
                                ->label(fn ($record) => $record->serviceOrder->currency_symbol . ' ' . number_format($record->service_price, 2, '.', ','))
                                ->alignRight()
                                ->size('md')
                                ->inline()
                                ->readOnly(),
                            TextLabel::make('')
                                ->label(trans('Additional Charges'))
                                ->alignLeft()
                                ->size('md')
                                ->inline()
                                ->readOnly(),
                            TextLabel::make('')
                                ->label(fn ($record, Closure $get) => $record->serviceOrder->currency_symbol . ' ' . number_format(array_reduce($get('additional_charges'), function ($carry, $data) {
                                    if (isset($data['price']) && is_numeric($data['price']) && isset($data['quantity']) && is_numeric($data['quantity'])) {
                                        return $carry + ($data['price'] * $data['quantity']);
                                    }

                                    return $carry;
                                }, 0), 2, '.', ','))
                                ->alignRight()
                                ->size('md')
                                ->inline()
                                ->readOnly(),
                            Forms\Components\Group::make()->columns(2)->columnSpan(2)->schema([
                                TextLabel::make('')
                                    ->label(fn ($record) => trans('Tax (') . $record->tax_percentage . '%)')
                                    ->alignLeft()
                                    ->size('md')
                                    ->inline()
                                    ->readOnly(),
                                TextLabel::make('')
                                    ->label(fn (ServiceBill $record, Closure $get) => $record->tax_display == PriceDisplay::INCLUSIVE->value ? 'Inclusive'
                                        :
                                        $record->serviceOrder->currency_symbol . ' ' .  number_format($record->tax_total, 2, '.', '.'))
                                    ->alignRight()
                                    ->size('md')
                                    ->inline()
                                    ->readOnly(),
                            ])->visible(
                                function (array $state) {
                                    return isset($state['tax_display']);
                                }
                            ),
                            TextLabel::make('')
                                ->label(trans('Total Price'))
                                ->alignLeft()
                                ->size('md')
                                ->inline()
                                ->readOnly()
                                ->color('primary'),
                            TextLabel::make('')
                                ->label(fn (ServiceBill $record, Closure $get) => $record->serviceOrder->currency_symbol . ' ' . number_format($record->total_amount, 2, '.', '.'))
                                ->alignRight()
                                ->size('md')
                                ->inline()
                                ->readOnly()
                                ->color('primary'),
                        ]),

                    ]),
                ])->columns(3),
        ];
    }

    private function getAdditionalCharges(): array
    {
        $schema = [];
        $schema[] = Forms\Components\Group::make()->columns(3)->schema([
            Support\TextLabel::make('')
                ->label(trans('Name'))
                ->alignLeft()
                ->size('sm')
                ->inline()
                ->readOnly(),
            Support\TextLabel::make('')
                ->label(trans('Quantity'))
                ->alignLeft()
                ->size('sm')
                ->inline()
                ->readOnly(),
            Support\TextLabel::make('')
                ->label(trans('Price'))
                ->alignLeft()
                ->size('sm')
                ->inline()
                ->readOnly(),

            Support\Divider::make('')->columnSpan(3),
        ]);
        foreach ($this->record->additional_charges as $additionalcharge) {
            $schema[] = Forms\Components\Group::make()->columns(3)->schema([
                Support\TextLabel::make('')
                    ->label($additionalcharge['name'])
                    ->alignLeft()
                    ->size('md')
                    ->inline()
                    ->readOnly(),
                Support\TextLabel::make('')
                    ->label('' . $additionalcharge['quantity'])
                    ->alignLeft()
                    ->size('md')
                    ->inline()
                    ->readOnly(),
                Support\TextLabel::make('')
                    ->label($this->record->serviceOrder->currency_symbol . $additionalcharge['price'])
                    ->alignLeft()
                    ->size('md')
                    ->inline()
                    ->readOnly(),
                Support\Divider::make('')->columnSpan(3),
            ]);
        }

        return $schema;
    }
}
