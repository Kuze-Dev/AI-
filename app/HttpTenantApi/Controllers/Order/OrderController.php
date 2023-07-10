<?php

declare(strict_types=1);

namespace App\HttpTenantApi\Controllers\Order;

use Domain\Customer\Models\Customer;
use Domain\Order\Actions\PlaceOrderAction;
use Domain\Order\DataTransferObjects\PlaceOrderData;
use Domain\Order\Enums\PlaceOrderResult;
use Domain\Order\Requests\PlaceOrderRequest;
use Spatie\RouteAttributes\Attributes\Middleware;
use Spatie\RouteAttributes\Attributes\Post;
use Spatie\RouteAttributes\Attributes\Prefix;

#[
    Prefix('orders'),
    Middleware(['auth:sanctum'])
]
class OrderController
{
    protected function isCostumerValidated(): bool
    {
        $customerId = auth()->user()?->id;

        $customer = Customer::where("id", $customerId)->whereStatus('active')->first();

        if (!$customer) {
            return false;
        }

        return true;
    }

    #[Post('/', 'orders.store')]
    public function store(PlaceOrderRequest $request)
    {
        $validatedData = $request->validated();

        $authenticated = $this->isCostumerValidated();

        if (!$authenticated) {
            return response()->json([
                'error' => "User Unauthorized",
            ], 403);
        }

        $result = app(PlaceOrderAction::class)
            ->execute(PlaceOrderData::fromArray($validatedData));

        return $result;

        if (PlaceOrderResult::SUCCESS != $result) {
            return response()->json([
                'error' => 'Bad Request',
                'message' => 'Order failed to be created'
            ], 400);
        }

        return response()
            ->json([
                'message' => 'Order placed successfully',
            ]);
    }
}
