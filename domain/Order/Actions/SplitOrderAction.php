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
use Domain\Product\Models\Product;
use Domain\Product\Models\ProductVariant;
use Illuminate\Support\Facades\DB;
use Exception;
use Illuminate\Database\Eloquent\Relations\MorphTo;
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

                // $this->createOrderLines($order, $placeOrderData);

                // $this->createOrderAddresses($order, $preparedOrderData);

                // CartLine::whereCheckoutReference($placeOrderData->cart_reference)
                //     ->update(['checked_out_at' => now()]);

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

        $subTotal = $preparedOrderData->cartLine->reduce(function ($carry, $cartLine) {
            $purchasable = $cartLine->purchasable;

            return $carry + ($purchasable->selling_price * $cartLine->quantity);
        }, 0);

        //add tax and minus discount here
        // $total = $subTotal + $preparedOrderData->totals->shipping_total;

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
            'sub_total' => $subTotal,
            'discount_total' => 0,
            'shipping_total' => 0,
            'total' => 0,

            'notes' => $preparedOrderData->notes,
            'shipping_method' => "test shipping_method",
            'shipping_details' => "test shipping details",
            'payment_method' => "COD",
            'payment_details' => "test payment details",
            'is_paid' => false
        ];
    }

    private function createOrderLines(Order $order, PlaceOrderData $placeOrderData)
    {
        $cartLines = CartLine::with(['purchasable' => function (MorphTo $query) {
            $query->morphWith([
                ProductVariant::class => ['product'],
            ]);
        },])
            ->whereCheckoutReference($placeOrderData->cart_reference)
            ->get();

        $orderLines = [];

        foreach ($cartLines as $cartLine) {
            $subTotal = 0;

            $subTotal = $cartLine->purchasable->selling_price * $cartLine->quantity;

            $name = null;

            if ($cartLine->purchasable instanceof Product) {
                $name = $cartLine->purchasable->name;
            } else if ($cartLine->purchasable instanceof ProductVariant) {
                $name = $cartLine->purchasable->product->name;
            }

            //add tax minus discount
            $total = 0 + $subTotal - 0;

            $orderLines = [
                'order_id' => $order->id,
                'purchasable_id' => $cartLine->purchasable_id,
                'purchasable_type' => $cartLine->purchasable_type,
                'purchasable_sku' => $cartLine->purchasable->sku,
                'name' => $name,
                'unit_price' => $cartLine->purchasable->selling_price,
                'quantity' => $cartLine->quantity,
                'tax_total' => 0,
                'sub_total' => $subTotal,
                'discount_total' => 0,
                'total' => $total,
                'remarks_data' => $cartLine->remarks,
                'purchasable_data' => $cartLine->purchasable
            ];

            $orderLine = OrderLine::create($orderLines);

            if ($cartLine->purchasable instanceof Product) {
                $cartLineMedias = $cartLine->purchasable->getMedia('image');
                foreach ($cartLineMedias as $cartLineMedia) {
                    $orderLine->addMediaFromUrl($cartLineMedia->getUrl())->toMediaCollection('order_line_images');
                }
            } else if ($cartLine->purchasable instanceof ProductVariant) {
                $cartLineMedias = $cartLine->purchasable->product->getMedia('image');
                foreach ($cartLineMedias as $cartLineMedia) {
                    $orderLine->addMediaFromUrl($cartLineMedia->getUrl())->toMediaCollection('order_line_images');
                }
            }


            // $remarkImageUrl = $cartLine->getFirstMediaUrl('cart_line_notes');
            // if (!empty($remarkImageUrl)) {
            //     $orderLine->addMediaFromUrl($remarkImageUrl)->toMediaCollection('order_line_notes');
            // }
        }
    }

    private function createOrderAddresses(Order $order, PreparedOrderData $preparedOrderData)
    {
        $addressesToInsert = [
            [
                'order_id' => $order->id,
                'type' => 'Shipping',
                'state' =>  $preparedOrderData->shipping_address->state ? $preparedOrderData->shipping_address->state->name : null,
                'label_as' =>  $preparedOrderData->shipping_address->label_as,
                'address_line_1' => $preparedOrderData->shipping_address->address_line_1,
                'zip_code' => $preparedOrderData->shipping_address->zip_code,
                'city' => $preparedOrderData->shipping_address->city,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'order_id' => $order->id,
                'type' => 'Billing',
                'state' =>  $preparedOrderData->billing_address->state ? $preparedOrderData->billing_address->state->name : null,
                'label_as' => 'test label as',
                'address_line_1' => $preparedOrderData->billing_address->address_line_1,
                'zip_code' => $preparedOrderData->billing_address->zip_code,
                'city' => $preparedOrderData->billing_address->city,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        OrderAddress::insert($addressesToInsert);
    }
}
