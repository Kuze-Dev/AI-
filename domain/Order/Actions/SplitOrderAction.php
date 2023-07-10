<?php

declare(strict_types=1);

namespace Domain\Order\Actions;

use Domain\Cart\Models\CartLine;
use Domain\Order\DataTransferObjects\PlaceOrderData;
use Domain\Order\DataTransferObjects\PreparedOrderData;
use Domain\Order\Enums\PlaceOrderResult;
use Domain\Order\Models\Order;
use Domain\Order\Models\OrderAddress;
use Domain\Order\Models\OrderLine;
use Illuminate\Support\Facades\DB;
use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class SplitOrderAction
{
    public function execute(PreparedOrderData $preparedOrderData, PlaceOrderData $placeOrderData): PlaceOrderResult|Exception
    {
        return DB::transaction(function () use ($preparedOrderData, $placeOrderData) {
            try {
                DB::beginTransaction();

                $orderPayload = $this->getOrderPayload($preparedOrderData);

                $order = Order::create($orderPayload);

                $this->createOrderLines($order, $placeOrderData);

                $this->createOrderAddresses($order, $preparedOrderData);

                CartLine::whereIn('id', $placeOrderData->cart_line_ids)
                    ->update(['checked_out_at' => now()]);

                DB::commit();

                return PlaceOrderResult::SUCCESS;
            } catch (\Exception $e) {
                DB::rollBack();
                Log::info('Error on SplitOrderAction->execute() ' . $e);
                return $e;
            }
        });
    }

    private function getOrderPayload(PreparedOrderData $preparedOrderData)
    {
        $referenceNumber = Str::upper(Str::random(12));

        //add tax and minus discount here
        $total = $preparedOrderData->totals->sub_total + $preparedOrderData->totals->shipping_total;

        return [
            'customer_id' => $preparedOrderData->customer->id,
            'customer_first_name' => $preparedOrderData->customer->first_name,
            'customer_last_name' => $preparedOrderData->customer->last_name,
            'customer_mobile' => $preparedOrderData->customer->mobile,
            'customer_email' => $preparedOrderData->customer->email,

            'currency_code' => $preparedOrderData->currency->code,
            'currency_name' => $preparedOrderData->currency->name,
            'currency_exchange_rate' => $preparedOrderData->currency->exchange_rate,

            'reference' =>  $referenceNumber,

            'tax_total' => 0,
            'sub_total' => $preparedOrderData->totals->sub_total,
            'discount_total' => 0,
            'shipping_total' => $preparedOrderData->totals->shipping_total,
            'total' => $total,

            'notes' => $preparedOrderData->notes,
            'shipping_method' => "test shipping_method",
            'payment_method' => "COD",
            'payment_details' => "test payment details",
            'is_paid' => false
        ];
    }

    private function createOrderLines(Order $order, PlaceOrderData $placeOrderData)
    {
        $cartLines = CartLine::with(['purchasable', 'variant'])
            ->whereIn('id', $placeOrderData->cart_line_ids)
            ->get();

        $orderLines = [];

        foreach ($cartLines as $cartLine) {
            $purchasableSku = "";
            $subTotal = 0;
            $unitPrice = 0;
            $variantData = null;

            if ($cartLine->variant) {
                $purchasableSku = $cartLine->variant->sku;
                $subTotal = (float) $cartLine->variant->selling_price * $cartLine->quantity;
                $unitPrice = (float) $cartLine->variant->selling_price;
                $variantData = $cartLine->variant;
            } else {
                $purchasableSku = $cartLine->purchasable->sku;
                $subTotal = $cartLine->purchasable->selling_price * $cartLine->quantity;
                $unitPrice = $cartLine->purchasable->selling_price;
            }

            //add tax minus discount
            $total = 0 + $subTotal - 0;

            $orderLines = [
                'order_id' => $order->id,
                'purchasable_id' => $cartLine->purchasable_id,
                'purchasable_type' => $cartLine->purchasable_type,
                'purchasable_sku' => $purchasableSku,
                'name' => $cartLine->purchasable->name,
                'unit_price' => $unitPrice,
                'quantity' => $cartLine->quantity,
                'tax_total' => 0,
                'sub_total' => $subTotal,
                'discount_total' => 0,
                'total' => $total,
                'notes' => $cartLine->notes,
                'purchasable_data' => $cartLine->purchasable,
                'variant_data' => $variantData,
                'created_at' => now(),
                'updated_at' => now(),
            ];

            $orderLine = OrderLine::create($orderLines);

            $imageUrl = $cartLine->purchasable->getFirstMediaUrl('image');
            $orderLine->addMediaFromUrl($imageUrl)->toMediaCollection('order_line_image');

            $remarkImageUrl = $cartLine->getFirstMediaUrl('cart_line_notes');
            if (!empty($remarkImageUrl)) {
                $orderLine->addMediaFromUrl($remarkImageUrl)->toMediaCollection('order_line_notes');
            }
        }
    }

    private function createOrderAddresses(Order $order, PreparedOrderData $preparedOrderData)
    {
        $addressesToInsert = [
            [
                'order_id' => $order->id,
                'type' => 'Shipping',
                'country' => $preparedOrderData->shipping_address->country->name,
                'state' =>  $preparedOrderData->shipping_address->state ? $preparedOrderData->shipping_address->state->name : null,
                'region' => $preparedOrderData->shipping_address->region ? $preparedOrderData->shipping_address->region->name : null,
                'city' => $preparedOrderData->shipping_address->city->name,
                'address_line_1' => $preparedOrderData->shipping_address->address_line_1,
                'address_line_2' => $preparedOrderData->shipping_address->address_line_2,
                'zip_code' => $preparedOrderData->shipping_address->zip_code,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'order_id' => $order->id,
                'type' => 'Billing',
                'country' => $preparedOrderData->billing_address->country->name,
                'state' =>  $preparedOrderData->billing_address->state ? $preparedOrderData->billing_address->state->name : null,
                'region' => $preparedOrderData->billing_address->region ? $preparedOrderData->billing_address->region->name : null,
                'city' => $preparedOrderData->billing_address->city->name,
                'address_line_1' => $preparedOrderData->billing_address->address_line_1,
                'address_line_2' => $preparedOrderData->billing_address->address_line_2,
                'zip_code' => $preparedOrderData->billing_address->zip_code,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        OrderAddress::insert($addressesToInsert);
    }
}
