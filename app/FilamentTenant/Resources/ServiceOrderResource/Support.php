<?php

declare(strict_types=1);

namespace App\FilamentTenant\Resources\ServiceOrderResource;

use Domain\Address\Models\Address;
use Domain\Currency\Models\Currency;
use Domain\Customer\Models\Customer;
use Domain\Service\Models\Service;
use Domain\ServiceOrder\Actions\CalculateServiceOrderTotalPriceAction;
use Domain\ServiceOrder\Actions\GetTaxableInfoAction;
use Domain\ServiceOrder\DataTransferObjects\ServiceOrderAdditionalChargeData;
use Domain\ServiceOrder\DataTransferObjects\ServiceOrderTaxData;
use Domain\Taxation\Enums\PriceDisplay;
use Filament\Forms\Get;

final class Support
{
    private function __construct() {}

    public static function customer(Get $get): Customer
    {
        return once(fn () => Customer::whereKey($get('customer_id'))->sole());
    }

    public static function service(Get $get): ?Service
    {
        $key = $get('service_id');

        if ($key === null) {
            return null;
        }

        return once(fn () => Service::whereKey($key)->first());
    }

    public static function ordinalNumber(int $number): string
    {
        if ($number % 100 >= 11 && $number % 100 <= 13) {
            $ordinal = $number.'th';
        } else {
            $ordinal = match ($number % 10) {
                1 => $number.'st',
                2 => $number.'nd',
                3 => $number.'rd',
                default => $number.'th',
            };
        }

        return $ordinal;
    }

    public static function getSubtotal(float $selling_price, array $additionalCharges): float
    {
        $subTotal = app(CalculateServiceOrderTotalPriceAction::class)
            ->execute(
                $selling_price,
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
            ->getAmount();

        return $subTotal;
    }

    public static function showTax(array $state): bool
    {
        $sellingPrice = Service::whereId($state['service_id'])->first()?->selling_price ?? 0;

        $taxDisplay = self::getTax(
            $sellingPrice,
            $state['additional_charges'],
            (int) ($state['is_same_as_billing'] ? $state['service_address'] : $state['billing_address'])
        )->tax_display;

        if (isset($taxDisplay)) {
            return true;
        }

        return false;
    }

    public static function currencyFormat(Get $get, string $type): string|float
    {
        $currencySymbol = Currency::whereEnabled(true)->firstOrFail()->symbol;
        $servicePrice = self::service($get)?->selling_price ?? 0;
        $additionalCharges = array_reduce($get('additional_charges'), function ($carry, $data) {
            if (isset($data['price']) && is_numeric($data['price']) && isset($data['quantity']) && is_numeric($data['quantity'])) {
                return $carry + ($data['price'] * $data['quantity']);
            }

            return $carry;
        }, 0);

        $taxInfo = (self::getTax(
            $servicePrice,
            $get('additional_charges'),
            (int) ($get('is_same_as_billing') ? $get('service_address') :
                $get('billing_address'))
        ));

        if ($taxInfo->tax_display == PriceDisplay::INCLUSIVE) {
            return PriceDisplay::INCLUSIVE->value;
        }

        $currency = 0.0;

        if ($type == 'servicePrice') {
            $currency = $servicePrice;
        } elseif ($type == 'additionalCharges') {
            $currency = $additionalCharges;
        } elseif ($type == 'taxPercentage') {
            return 'Tax ('.$taxInfo->tax_percentage.'%)';
        } elseif ($type == 'totalPrice') {
            $currency = $taxInfo->total_price;
        } elseif ($type == 'taxTotal') {
            $currency = $taxInfo->tax_total;
        } elseif ($type == 'totalPriceFloat') {
            return floatval($taxInfo->total_price);
        }

        return $currencySymbol.' '.number_format($currency, 2, '.', ',');
    }

    public static function getTax(float $selling_price, array $additionalCharges, ?int $billing_address_id): ServiceOrderTaxData
    {
        $subTotal = self::getSubtotal($selling_price, $additionalCharges);

        if (is_null($billing_address_id) || $billing_address_id === 0) {
            return new ServiceOrderTaxData(
                sub_total: $subTotal,
                tax_display: null,
                tax_percentage: 0,
                tax_total: 0,
                total_price: $subTotal
            );
        }

        $billingAddressData = Address::whereId($billing_address_id)
            ->firstOrFail();

        return app(GetTaxableInfoAction::class)
            ->execute($subTotal, $billingAddressData);
    }
}
