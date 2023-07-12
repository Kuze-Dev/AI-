<?php

declare(strict_types=1);

namespace Domain\Order\Actions;

use Domain\Cart\Models\CartLine;
use Domain\Order\DataTransferObjects\PlaceOrderData;
use Domain\Order\DataTransferObjects\PreparedOrderData;
use Domain\Order\Enums\OrderResult;
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
    public function execute(PreparedOrderData $preparedOrderData, PlaceOrderData $placeOrderData): OrderResult|Exception
    {
        return DB::transaction(function () use ($preparedOrderData, $placeOrderData) {
            try {
                DB::beginTransaction();

                $order = app(CreateOrderAction::class)
                    ->execute($preparedOrderData);

                app(CreateOrderLineAction::class)
                    ->execute($order, $preparedOrderData);

                app(CreateOrderAddressAction::class)
                    ->execute($order, $preparedOrderData);

                CartLine::whereCheckoutReference($placeOrderData->cart_reference)
                    ->update(['checked_out_at' => now()]);

                DB::commit();

                return OrderResult::SUCCESS;
            } catch (\Exception $e) {
                DB::rollBack();
                Log::info('Error on SplitOrderAction->execute() ' . $e);
                return $e;
            }
        });
    }
}
