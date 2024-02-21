<?php

declare(strict_types=1);

namespace App\FilamentTenant\Resources\ServiceOrderResource\Pages;

use App\Filament\Pages\Concerns\LogsFormActivity;
use App\FilamentTenant\Resources\ServiceOrderResource;
use Domain\Address\Models\Address;
use Domain\Currency\Models\Currency;
use Domain\Service\Models\Service;
use Domain\ServiceOrder\Actions\CalculateServiceOrderTotalPriceAction;
use Domain\ServiceOrder\Actions\GenerateReferenceNumberAction;
use Domain\ServiceOrder\Actions\GetTaxableInfoAction;
use Domain\ServiceOrder\Actions\ServiceOrderCreatedPipelineAction;
use Domain\ServiceOrder\Actions\ServiceOrderMilestoneCreatedPipelineAction;
use Domain\ServiceOrder\DataTransferObjects\ServiceOrderAdditionalChargeData;
use Domain\ServiceOrder\DataTransferObjects\ServiceOrderCreatedPipelineData;
use Domain\ServiceOrder\DataTransferObjects\ServiceOrderTaxData;
use Domain\ServiceOrder\Enums\PaymentPlanType;
use Domain\ServiceOrder\Enums\ServiceOrderStatus;
use Domain\ServiceOrder\Models\ServiceOrder;
use Filament\Actions\Action;
use Filament\Facades\Filament;
use Filament\Resources\Pages\CreateRecord;

/**
 * @property-read \Domain\ServiceOrder\Models\ServiceOrder $record
 */
class CreateServiceOrder extends CreateRecord
{
    use LogsFormActivity;

    protected static string $resource = ServiceOrderResource::class;

    private static bool $is_same_as_billing;

    private static int|string|null $service_address;

    private static int|string|null $billing_address;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('create')
                ->label(trans('filament-panels::resources/pages/create-record.form.actions.create.label'))
                ->action('create')
                ->keyBindings(['mod+s']),
        ];
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $rawData = $this->form->getRawState();

        $data['admin_id'] = Filament::auth()->id();
        $data['reference'] = app(GenerateReferenceNumberAction::class)
            ->execute(ServiceOrder::class);

        $service = Service::whereKey($data['service_id'])->sole();
        $data['schema'] = $service->blueprint?->schema;
        $data['service_name'] = $service->name;
        $data['retail_price'] = $service->retail_price;
        $data['service_price'] = $service->selling_price;
        $data['billing_cycle'] = $service->billing_cycle;
        $data['due_date_every'] = $service->due_date_every;
        $data['pay_upfront'] = $service->pay_upfront;
        $data['is_subscription'] = $service->is_subscription;
        $data['needs_approval'] = $service->needs_approval;
        $data['is_auto_generated_bill'] = $service->is_auto_generated_bill;
        $data['is_partial_payment'] = $service->is_partial_payment;
        $data['status'] = ServiceOrderStatus::fromService($service);

        $currency = Currency::whereEnabled(true)->sole();
        $data['currency_code'] = $currency->code;
        $data['currency_name'] = $currency->name;
        $data['currency_symbol'] = $currency->symbol;

        $taxableInfo = self::getTax(
            $rawData,
            self::getSubTotalPrice(
                $service->selling_price,
                $data['additional_charges'] ?? []
            )
        );
        $data['sub_total'] = $taxableInfo->sub_total;
        $data['tax_display'] = $taxableInfo->tax_display;
        $data['tax_percentage'] = $taxableInfo->tax_percentage;
        $data['tax_total'] = $taxableInfo->tax_total;
        $data['total_price'] = $taxableInfo->total_price;

        // for afterCreate
        self::$is_same_as_billing = $rawData['is_same_as_billing'];
        self::$service_address = $rawData['service_address'];
        self::$billing_address = $rawData['billing_address'];

        return $data;
    }

    public function afterCreate(): void
    {
        // TODO: useless dto
        $dto = new ServiceOrderCreatedPipelineData(
            serviceOrder: $this->record,
            service_address_id: self::$service_address,
            billing_address_id: self::$billing_address,
            is_same_as_billing: self::$is_same_as_billing
        );

        if ($this->record->payment_type === PaymentPlanType::MILESTONE) {
            app(ServiceOrderMilestoneCreatedPipelineAction::class)
                ->execute($dto, createServiceOrderAddress: false);
        } else {
            app(ServiceOrderCreatedPipelineAction::class)
                ->execute($dto, createServiceOrderAddress: false);
        }
    }

    private static function getTax(
        array $rawData,
        int|float $subTotalPrice
    ): ServiceOrderTaxData {

        $billingAddressId = $rawData['is_same_as_billing'] === true
            ? $rawData['service_address']
            : $rawData['billing_address'];

        return app(GetTaxableInfoAction::class)
            ->execute(
                $subTotalPrice,
                Address::whereKey($billingAddressId)->sole()
            );
    }

    private static function getSubTotalPrice(float $sellingPrice, array $additionalCharges): int|float
    {
        return app(CalculateServiceOrderTotalPriceAction::class)
            ->execute(
                $sellingPrice,
                array_filter(
                    array_map(
                        function ($additionalCharge) {
                            if (
                                isset($additionalCharge['price']) &&
                                is_numeric($additionalCharge['price']) &&
                                isset($additionalCharge['quantity']) &&
                                is_numeric($additionalCharge['quantity'])
                            ) {
                                return new ServiceOrderAdditionalChargeData(
                                    (float) $additionalCharge['price'],
                                    (int) $additionalCharge['quantity']
                                );
                            }
                        },
                        $additionalCharges
                    )
                )
            )
            ->getAmount();
    }
}
