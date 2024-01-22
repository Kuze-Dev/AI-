<?php

declare(strict_types=1);

namespace Domain\ServiceOrder\Actions;

use Domain\Address\Models\Address;
use Domain\Admin\Models\Admin;
use Domain\Currency\Models\Currency;
use Domain\Customer\Models\Customer;
use Domain\Service\Models\Service;
use Domain\ServiceOrder\DataTransferObjects\ServiceOrderAdditionalChargeData;
use Domain\ServiceOrder\DataTransferObjects\ServiceOrderCreatedPipelineData;
use Domain\ServiceOrder\DataTransferObjects\ServiceOrderData;
use Domain\ServiceOrder\DataTransferObjects\ServiceOrderTaxData;
use Domain\ServiceOrder\Enums\PaymentPlanType;
use Domain\ServiceOrder\Enums\PaymentPlanValue;
use Domain\ServiceOrder\Enums\ServiceOrderStatus;
use Domain\ServiceOrder\Exceptions\InvalidPaymentPlan;
use Domain\ServiceOrder\Exceptions\ServiceStatusMustBeActive;
use Domain\ServiceOrder\Models\ServiceOrder;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Auth;

class CreateServiceOrderAction
{
    public function __construct(
        private GenerateReferenceNumberAction $generateReferenceNumberAction,
        private CalculateServiceOrderTotalPriceAction $calculateServiceOrderTotalPriceAction,
        private GetTaxableInfoAction $getTaxableInfoAction,
        private ServiceOrderCreatedPipelineAction $serviceOrderCreatedPipelineAction,
        private ServiceOrderMilestoneCreatedPipelineAction $serviceOrderMilestoneCreatedPipelineAction
    ) {
    }

    public function execute(ServiceOrderData $serviceOrderData): ServiceOrder
    {
        $customer = Customer::whereId($serviceOrderData->customer_id)->firstOrFail();

        $service = Service::whereId($serviceOrderData->service_id)->firstOrFail();

        $currency = Currency::whereEnabled(true)->firstOrFail();

        if (! $service->status) {
            throw new ServiceStatusMustBeActive();
        }

        $subTotalPrice = $this->getSubTotalPrice(
            $service->selling_price,
            $serviceOrderData->additional_charges
        );

        $taxableInfo = $this->getTax($serviceOrderData, $subTotalPrice);

        $paymentPlan = $serviceOrderData->payment_plan;
        if (is_null($paymentPlan)) {
            throw new ModelNotFoundException();
        }
        $amounts = array_column($paymentPlan, 'amount');
        $sum = array_sum(array_map('floatval', $amounts));

        if ($serviceOrderData->payment_value === PaymentPlanValue::FIXED->value) {
            if ($sum > $taxableInfo->total_price) {
                throw new InvalidPaymentPlan('The payment plan exceeds the total price');
            }
        } elseif ($serviceOrderData->payment_value === PaymentPlanValue::PERCENT->value) {
            if ($sum !== floatval(100)) {
                throw new InvalidPaymentPlan('The payment plan amount must be equal to 100');
            }
        }

        $serviceOrder = ServiceOrder::create([
            'admin_id' => Auth::user() instanceof Admin && Auth::user()->hasRole(config('domain.role.super_admin'))
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

            'schema' => $service->blueprint?->schema,
            'customer_form' => $serviceOrderData->form,
            'currency_code' => $currency->code,
            'currency_name' => $currency->name,
            'currency_symbol' => $currency->symbol,
            'service_name' => $service->name,
            'retail_price' => $service->retail_price,
            'service_price' => $service->selling_price,
            'billing_cycle' => $service->billing_cycle,
            'due_date_every' => $service->due_date_every,
            'pay_upfront' => $service->pay_upfront,
            'is_subscription' => $service->is_subscription,
            'needs_approval' => $service->needs_approval,
            'is_auto_generated_bill' => $service->is_auto_generated_bill,
            'is_partial_payment' => $service->is_partial_payment,
            'schedule' => $serviceOrderData->schedule,
            'status' => $this->getStatus($service),
            'additional_charges' => $serviceOrderData->additional_charges,
            'sub_total' => $taxableInfo->sub_total,
            'tax_display' => $taxableInfo->tax_display,
            'tax_percentage' => $taxableInfo->tax_percentage,
            'tax_total' => $taxableInfo->tax_total,
            'total_price' => $taxableInfo->total_price,
            'payment_type' => $serviceOrderData->payment_type,
            'payment_value' => $serviceOrderData->payment_value,
            'payment_plan' => $serviceOrderData->payment_plan,
        ]);

        if ($serviceOrderData->payment_type === PaymentPlanType::MILESTONE->value) {
            $this->serviceOrderMilestoneCreatedPipelineAction->execute(
                new ServiceOrderCreatedPipelineData(
                    serviceOrder: $serviceOrder,
                    service_address_id: $serviceOrderData->service_address_id,
                    billing_address_id: $serviceOrderData->billing_address_id,
                    is_same_as_billing: $serviceOrderData->is_same_as_billing
                )
            );
        } else {
            $this->serviceOrderCreatedPipelineAction->execute(
                new ServiceOrderCreatedPipelineData(
                    serviceOrder: $serviceOrder,
                    service_address_id: $serviceOrderData->service_address_id,
                    billing_address_id: $serviceOrderData->billing_address_id,
                    is_same_as_billing: $serviceOrderData->is_same_as_billing
                )
            );
        }

        return $serviceOrder;
    }

    public function getStatus(Service $service): ServiceOrderStatus
    {
        $status = ServiceOrderStatus::FORPAYMENT;

        if ($service->needs_approval) {
            $status = ServiceOrderStatus::PENDING;
        } elseif (! $service->pay_upfront && ! $service->is_subscription) {
            $status = ServiceOrderStatus::INPROGRESS;
        }

        return $status;
    }

    public function getTax(
        ServiceOrderData $serviceOrderData,
        int|float $subTotalPrice
    ): ServiceOrderTaxData {

        $billingAddressId = $serviceOrderData->is_same_as_billing
            ? $serviceOrderData->service_address_id
            : $serviceOrderData->billing_address_id;

        $billingAddress = Address::whereId($billingAddressId)->firstOrFail();

        return $this->getTaxableInfoAction
            ->execute(
                $subTotalPrice,
                $billingAddress
            );
    }

    public function getSubTotalPrice(float $sellingPrice, array $additionalCharges): int|float
    {
        return $this->calculateServiceOrderTotalPriceAction
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
