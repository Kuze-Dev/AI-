<?php

namespace Domain\Support\Payments\DataTransferObjects;


class PaypalAmountData
{

    public function __construct(
        public readonly PaypalDetailsData $details,
        public readonly string $currency,
        public readonly string $total,
      
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            currency: $data['currency'],
            total: $data['total'],
            details: $data['details'],
            // details: array_map(
            //     fn (array $detail) => new PaypalDetailsData(
            //         subtotal: $detail['subtotal'] ?? null,
            //         shipping: $detail['shipping'] ?? null,
            //         tax: $detail['tax'] ?? null,
            //         handling_fee: $detail['handling_fee'] ?? null,
            //         shipping_discount: $detail['shipping_discount'] ?? null,
            //         insurance: $detail['insurance'] ?? null,
            //         gift_wrap: $detail['gift_wrap'] ?? null,
            //         fee: $detail['fee'] ?? null,
            //     ),
            //     $data['details'] ?? [],
            // ),            
          
        );
    }
}


