<?php

declare(strict_types=1);

namespace App\HttpTenantApi\Controllers\Cart;

use App\Http\Controllers\Controller;
use Domain\Cart\Actions\CartSummaryAction;
use Domain\Cart\DataTransferObjects\CartSummaryShippingData;
use Domain\Cart\DataTransferObjects\CartSummaryTaxData;
use Domain\Cart\Models\CartLine;
use Domain\Cart\Requests\CartSummaryRequest;
use Domain\Shipment\API\USPS\Exceptions\USPSServiceNotFoundException;
use Spatie\RouteAttributes\Attributes\Get;
use Spatie\RouteAttributes\Attributes\Middleware;
use Throwable;

#[
    Middleware(['auth:sanctum'])
]
class CartSummaryController extends Controller
{
    #[Get('carts/count', name: 'carts.count')]
    public function count(): mixed
    {
        $cartLinesCount = CartLine::query()
            ->whereHas('cart', function ($query) {
                $query->whereBelongsTo(auth()->user());
            })
            ->whereNull('checked_out_at')
            ->count();

        return response()->json(['cartCount' => $cartLinesCount], 200);
    }

    #[Get('carts/summary', name: 'carts.summary')]
    public function summary(CartSummaryRequest $request): mixed
    {
        $validated = $request->validated();
        $discountCode = $validated['discount_code'] ?? null;

        /** @var \Domain\Customer\Models\Customer $customer */
        $customer = auth()->user();

        $cartLines = $request->getCartLines();

        $country = $request->getCountry();
        $state = $request->getState();
        $discount = $request->getDiscount();
        $serviceId = $validated['service_id'] ?? null;

        try {
            $summary = app(CartSummaryAction::class)->getSummary(
                $cartLines,
                new CartSummaryTaxData($country?->id, $state?->id),
                new CartSummaryShippingData($customer, $request->getShippingAddress(), $request->getShippingMethod()),
                $discount,
                $serviceId ? (int) $serviceId : null
            );
        } catch (Throwable $th) {
            if ($th instanceof USPSServiceNotFoundException) {
                return response()->json([
                    'service_id' => 'Shipping method service id is required',
                ], 404);
            } else {
                return response()->json([
                    'message' => $th->getMessage(),
                ], 422);
            }
        }

        $responseArray = [
            'tax' => [
                'inclusive_sub_total' => $summary->taxTotal ? round($summary->subTotal + $summary->taxTotal, 2) : null,
                'display' => $summary->taxTotal ? $summary->taxDisplay : null,
                'percentage' => $summary->taxPercentage ? round($summary->taxPercentage, 2) : 0,
                'amount' => $summary->taxTotal ? round($summary->taxTotal, 2) : 0,
            ],
            'sub_total' => round($summary->subTotal, 2),
            'shipping_fee' => round($summary->shippingTotal, 2),
            'total' => round($summary->grandTotal, 2),
            'discount' => [
                'status' => $summary->discountMessages->status ?? null,
                'message' => $summary->discountMessages->message ?? null,
                'type' => $summary->discountMessages->amount_type ?? null,
                'amount' => $summary->discountMessages->amount ?? null,
                'discount_type' => $summary->discountMessages->discount_type ?? null,
                'total_savings' => $discount ? round($summary->discountTotal ?? 0, 2) : 0,
            ]
        ];

        if (!$discountCode) {
            unset($responseArray['discount']);
        }

        return response()->json($responseArray, 200);
    }
}
