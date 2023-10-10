<?php

declare(strict_types=1);

namespace App\FilamentTenant\Resources\ServiceOrderResource\Pages;

use App\Filament\Pages\Concerns\LogsFormActivity;
use App\FilamentTenant\Resources\ServiceOrderResource;
use Closure;
use Filament\Forms;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Section;
use Filament\Resources\Pages\ViewRecord;
use App\FilamentTenant\Support;
use App\FilamentTenant\Support\SchemaFormBuilder;
use Domain\ServiceOrder\Enums\ServiceOrderAddressType;
use Domain\ServiceOrder\Models\ServiceOrderAddress;
use Illuminate\Contracts\Support\Htmlable;

class ViewServiceOrder extends ViewRecord
{
    use LogsFormActivity;

    protected static string $resource = ServiceOrderResource::class;

    protected function getHeading(): string|Htmlable
    {
        return trans('Service Order Details #') . $this->record->reference;
    }

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
                        ->content(fn ($record) => $record->service->billing_cycle),
                    Placeholder::make('Due date every')
                        ->content(fn ($record) => $record->service->due_date_every),
                ])->visible(fn ($record) => $record->service->is_subscription),
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
                    ]),
                ]),

            Section::make(trans('Service Address'))
                ->schema([
                    Forms\Components\Group::make()->columns(2)->schema([
                        Placeholder::make('House/Unit/Flr #, Bldg Name, Blk or Lot #')
                            ->content(fn ($record) => ServiceOrderAddress::whereServiceOrderId($record->id)->whereType(ServiceOrderAddressType::SERVICE_ADDRESS)->first()->address_line_1 ?? null),
                        Placeholder::make('Country')
                            ->content(fn ($record) => ServiceOrderAddress::whereServiceOrderId($record->id)->whereType(ServiceOrderAddressType::SERVICE_ADDRESS)->first()->country ?? null),
                        Placeholder::make('State')
                            ->content(fn ($record) => ServiceOrderAddress::whereServiceOrderId($record->id)->whereType(ServiceOrderAddressType::SERVICE_ADDRESS)->first()->state ?? null),
                        Placeholder::make('City/Province')
                            ->content(fn ($record) => ServiceOrderAddress::whereServiceOrderId($record->id)->whereType(ServiceOrderAddressType::SERVICE_ADDRESS)->first()->city ?? null),
                        Placeholder::make('Zip Code')
                            ->content(fn ($record) => ServiceOrderAddress::whereServiceOrderId($record->id)->whereType(ServiceOrderAddressType::SERVICE_ADDRESS)->first()->zip_code ?? null),
                    ]),
                ]),

            Section::make(trans('Billing Address'))
                ->schema([
                    Forms\Components\Group::make()->columns(2)->schema([
                        Placeholder::make('House/Unit/Flr #, Bldg Name, Blk or Lot #')
                            ->content(fn ($record) => ServiceOrderAddress::whereServiceOrderId($record->id)->whereType(ServiceOrderAddressType::BILLING_ADDRESS)->first()->address_line_1 ?? null),
                        Placeholder::make('Country')
                            ->content(fn ($record) => ServiceOrderAddress::whereServiceOrderId($record->id)->whereType(ServiceOrderAddressType::BILLING_ADDRESS)->first()->country ?? null),
                        Placeholder::make('State')
                            ->content(fn ($record) => ServiceOrderAddress::whereServiceOrderId($record->id)->whereType(ServiceOrderAddressType::BILLING_ADDRESS)->first()->state ?? null),
                        Placeholder::make('City/Province')
                            ->content(fn ($record) => ServiceOrderAddress::whereServiceOrderId($record->id)->whereType(ServiceOrderAddressType::BILLING_ADDRESS)->first()->city ?? null),
                        Placeholder::make('Zip Code')
                            ->content(fn ($record) => ServiceOrderAddress::whereServiceOrderId($record->id)->whereType(ServiceOrderAddressType::BILLING_ADDRESS)->first()->zip_code ?? null),
                    ]),
                ]),

            Section::make(trans('Additional Charges'))
                ->schema([
                    Forms\Components\Group::make()->schema($this->getAdditionalCharges()),
                ])->visible(fn ($record) => ! empty($record->additional_charges)),

            Section::make(trans('Service Fill-up Form'))
                ->schema([
                    SchemaFormBuilder::make('customer_form', fn ($record) => $record->service->blueprint->schema)
                        ->schemaData(fn ($record) => $record->service->blueprint->schema),
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
