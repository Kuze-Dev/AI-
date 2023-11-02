<?php

declare(strict_types=1);

namespace Domain\ServiceOrder\Actions;

use Domain\Address\Models\Address;
use Domain\Currency\Models\Currency;
use Domain\Customer\Models\Customer;
use Domain\Service\Models\Service;
use Domain\ServiceOrder\DataTransferObjects\ServiceOrderAdditionalChargeData;
use Domain\ServiceOrder\DataTransferObjects\ServiceOrderData;
use Domain\ServiceOrder\DataTransferObjects\ServiceOrderTaxData;
use Domain\ServiceOrder\Enums\ServiceOrderStatus;
use Domain\ServiceOrder\Models\ServiceOrder;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class CreateServiceOrderAction
{
    public function __construct(
        private GenerateReferenceNumberAction $generateReferenceNumberAction,
        private CalculateServiceOrderTotalPriceAction $calculateServiceOrderTotalPriceAction,
        private GetTaxableInfoAction $getTaxableInfoAction,
    ) {
    }

    public function execute(
        ServiceOrderData $serviceOrderData
    ): ServiceOrder {

        $customer = Customer::whereId($serviceOrderData->customer_id)
            ->first();

        $service = Service::whereId($serviceOrderData->service_id)
            ->first();

        $currency = Currency::whereEnabled(true)->first();

        if (! ($customer instanceof Customer)) {
            throw new BadRequestHttpException('Customer not found');
        } elseif (! ($service instanceof Service)) {
            throw new BadRequestHttpException('Service not found');
        } elseif (! ($currency instanceof Currency)) {
            throw new BadRequestHttpException('Currency not found');
        }

        if (! $service->status) {
            throw new BadRequestHttpException('inactive service found');
        }

        /** @var int|float $subTotal */
        $subTotal = $this->calculateServiceOrderTotalPriceAction
            ->execute(
                $service->selling_price,
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
                        $serviceOrderData->additional_charges ?? []
                    )
                )
            )
            ->getAmount();

        $taxableInfo = $this->getTax($serviceOrderData, $subTotal);

        $serviceOrder = ServiceOrder::create([
            'admin_id' => Auth::user()?->hasRole(config('domain.role.super_admin'))
                ? Auth::id()
                : null,
            'service_id' => $serviceOrderData->service_id,
            'customer_id' => $serviceOrderData->customer_id,
            'reference' => $this->generateReferenceNumberAction
                ->execute(new ServiceOrder()),
            'customer_first_name' => $customer->first_name,
            'customer_last_name' => $customer->last_name,
            'customer_email' => $customer->email,
            'customer_mobile' => $customer->mobile,
            'customer_form' => $serviceOrderData->form,
            'currency_code' => $currency->code,
            'currency_name' => $currency->name,
            'currency_symbol' => $currency->symbol,
            'service_name' => $service->name,
            'service_price' => $service->selling_price,
            'billing_cycle' => $service->billing_cycle,
            'due_date_every' => $service->due_date_every,
            'pay_upfront' => $service->pay_upfront,
            'is_subscription' => $service->is_subscription,
            'needs_approval' => $service->needs_approval,
            'is_auto_generated_bill' => $service->is_auto_generated_bill,
            'schedule' => $serviceOrderData->schedule,
            'status' => $this->getStatus($service),
            'additional_charges' => $serviceOrderData->additional_charges,
            'sub_total' => $taxableInfo->sub_total,
            'tax_display' => $taxableInfo->tax_display,
            'tax_percentage' => $taxableInfo->tax_percentage,
            'tax_total' => $taxableInfo->tax_total,
            'total_price' => $taxableInfo->total_price,
        ]);

        return $serviceOrder;
    }

    public function getStatus(Service $service): ServiceOrderStatus
    {
        $status = ServiceOrderStatus::FORPAYMENT;

        if ($service->needs_approval) {
            $status = ServiceOrderStatus::PENDING;
        } elseif (
            ! $service->pay_upfront && ! $service->is_subscription
        ) {
            $status = ServiceOrderStatus::INPROGRESS;
        }

        return $status;
    }

    public function getTax(
        ServiceOrderData $serviceOrderData,
        int|float $subTotal
    ): ServiceOrderTaxData {
        $billingAddressId = $serviceOrderData->is_same_as_billing
            ? $serviceOrderData->service_address_id
            : $serviceOrderData->billing_address_id;

        $billingAddressData = Address::whereId($billingAddressId)
            ->firstOrFail();

        return $this->getTaxableInfoAction
            ->execute($subTotal, $billingAddressData);
    }
}
