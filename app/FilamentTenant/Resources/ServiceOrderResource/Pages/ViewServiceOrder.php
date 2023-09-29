<?php

declare(strict_types=1);

namespace App\FilamentTenant\Resources\ServiceOrderResource\Pages;

use App\Filament\Pages\Concerns\LogsFormActivity;
use App\FilamentTenant\Resources\ServiceOrderResource;
use Closure;
use Domain\Address\Models\Address;
use Domain\Service\Models\Service;
use Filament\Forms;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Section;
use Filament\Resources\Pages\ViewRecord;
use App\FilamentTenant\Support;
use App\FilamentTenant\Support\SchemaFormBuilder;

class ViewServiceOrder extends ViewRecord
{
    use LogsFormActivity;

    protected static string $resource = ServiceOrderResource::class;

    protected function getFormSchema(): array
    {
        return [
            Forms\Components\Group::make()
                ->schema([
                    Forms\Components\Group::make()
                        ->schema($this->getSection())->columnSpan(2),
                    ServiceOrderResource::summaryCard()->columnSpan(1),
                ])->columns(3),
        ];
    }

    private function getSection(): array
    {
        return [Section::make(trans('Service'))->schema([
            Grid::make(2)->schema([
                Placeholder::make('service')
                    ->content(fn ($record) => $record->service_name),
                Placeholder::make('service Price')
                    ->content(fn ($record) => $record->service_price),
                Forms\Components\Group::make()->columns(2)->columnSpan(2)->schema([
                    Placeholder::make('BillingCycle')
                        ->content(fn ($record) => Service::whereId($record->service_id)->first()->billing_cycle),
                    Placeholder::make('Recurring payment')
                        ->content(fn ($record) => Service::whereId($record->service_id)->first()->recurring_payment),
                ])->visible(fn ($record) => Service::whereId($record->service_id)->first()->is_subscription),
            ]),
        ]),
            Section::make(trans('Customer'))
                ->schema([
                    Forms\Components\Group::make()->columns(2)->schema([
                        Placeholder::make('first_name')
                            ->content(fn ($record) => $record->customer_first_name),
                        Placeholder::make('last_name')
                            ->content(fn ($record) => $record->customer_last_name),
                        Placeholder::make('email')
                            ->content(fn ($record) => $record->customer_email),
                        Placeholder::make('mobile')
                            ->content(fn ($record) => $record->customer_mobile),
                        Placeholder::make('billing_address')
                            ->content(fn ($record) => Address::whereCustomerId($record->customer_id)->first()->address_line_1),
                    ]),

                ]),

            Section::make(trans('Additional Charges'))
                ->schema([
                    Forms\Components\Group::make()->schema($this->getAdditionalCharges()),
                ]),

            Section::make(trans('Service Fill-up Form'))
                ->schema([
                    SchemaFormBuilder::make('customer_form', fn ($record) => Service::whereId($record->service_id)->first()?->blueprint->schema)
                        ->schemaData(fn ($record) => Service::whereId($record->service_id)->first()?->blueprint->schema),
                ])
                ->hidden(fn (Closure $get) => $get('service_id') === null)
                ->columnSpan(2),

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
        foreach($this->record->additional_charges as $additionalcharge) {
            $schema[] = Forms\Components\Group::make()->columns(3)->schema([
                Support\TextLabel::make('')
                    ->label($additionalcharge['name'])
                    ->alignLeft()
                    ->size('md')
                    ->inline()
                    ->readOnly(),
                Support\TextLabel::make('')
                    ->label('' .$additionalcharge['quantity'])
                    ->alignLeft()
                    ->size('md')
                    ->inline()
                    ->readOnly(),
                Support\TextLabel::make('')
                    ->label($this->record->currency_symbol .$additionalcharge['price'])
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
