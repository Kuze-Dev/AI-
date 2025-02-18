<?php

declare(strict_types=1);

namespace App\HttpTenantApi\Controllers\Cart\PrivateCart;

use App\Attributes\CurrentApiCustomer;
use App\Http\Controllers\Controller;
use Domain\Cart\Actions\CartSummaryAction;
use Domain\Cart\DataTransferObjects\CartSummaryShippingData;
use Domain\Cart\DataTransferObjects\CartSummaryTaxData;
use Domain\Cart\Events\SanitizeCartEvent;
use Domain\Cart\Models\CartLine;
use Domain\Cart\Requests\CartSummaryRequest;
use Domain\Customer\Models\Customer;
use Domain\Shipment\API\AusPost\Exceptions\AusPostServiceNotFoundException;
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
        $cartLines = CartLine::with('purchasable')
            ->whereHas('cart', function ($query) {
                $query->whereBelongsTo(auth()->user());
            })
            ->whereNull('checked_out_at')
            ->get();

        if ($cartLines->count()) {
            $cartLineIdsTobeRemoved = [];

            $cartLines = $cartLines->filter(function ($cartLine) use (&$cartLineIdsTobeRemoved) {
                if ($cartLine->purchasable === null) {
                    $cartLineIdsTobeRemoved[] = $cartLine->uuid;
                }

                return $cartLine->purchasable !== null;
            });

            if (! empty($cartLineIdsTobeRemoved)) {
                event(new SanitizeCartEvent(
                    $cartLineIdsTobeRemoved,
                ));
            }
        }

        return response()->json(['cartCount' => $cartLines->count()], 200);
    }

    #[Get('carts/summary', name: 'carts.summary')]
    public function summary(CartSummaryRequest $request,#[CurrentApiCustomer] Customer $customer): mixed
    {
        $validated = $request->validated();
        $discountCode = $validated['discount_code'] ?? null;


        $cartLines = $request->getCartLines();

        $country = $request->getCountry();
        $state = $request->getState();
        $discount = $request->getDiscount();
        $serviceId = $validated['service_id'] ?? null;

        try {
            $summary = app(CartSummaryAction::class)->execute(
                $cartLines,
                new CartSummaryTaxData($country?->id, $state?->id),
                new CartSummaryShippingData($customer, $request->getShippingAddress(), $request->getShippingMethod()),
                $discount,
                $serviceId ?: null
            );

            $responseArray = [
                'tax' => [
                    'display' => $summary->taxDisplay,
                    'percentage' => $summary->taxPercentage ? round($summary->taxPercentage, 2) : 0,
                    'amount' => $summary->taxTotal ? number_format((float) $summary->taxTotal, 2, '.', ',') : 0,
                ],
                'sub_total' => [
                    'initial_amount' => number_format((float) $summary->initialSubTotal, 2, '.', ','),
                    'discounted_amount' => number_format((float) $summary->subTotal, 2, '.', ','),
                ],
                'shipping_fee' => [
                    'initial_amount' => number_format((float) $summary->initialShippingTotal, 2, '.', ','),
                    'discounted_amount' => number_format((float) $summary->shippingTotal, 2, '.', ','),
                ],
                'total' => number_format((float) $summary->grandTotal, 2, '.', ','),
                'discount' => [
                    'status' => $summary->discountMessages->status ?? null,
                    'message' => $summary->discountMessages->message ?? null,
                    'type' => $summary->discountMessages->amount_type ?? null,
                    'amount' => $summary->discountMessages ? number_format((float) $summary->discountMessages->amount, 2, '.', ',') : null,
                    'discount_type' => $summary->discountMessages->discount_type ?? null,
                    'total_savings' => $discount ? number_format((float) $summary->discountTotal, 2, '.', ',') : 0,
                ],
            ];

            if (! $discountCode) {
                unset($responseArray['discount']);
            }

            return response()->json($responseArray, 200);
        } catch (Throwable $th) {
            if (
                $th instanceof USPSServiceNotFoundException ||
                $th instanceof AusPostServiceNotFoundException
            ) {
                return response()->json([
                    'service_id' => 'Shipping method service id is required',
                ], 404);
            } else {
                return response()->json([
                    'message' => $th->getMessage(),
                ], 422);
            }
        }
    }
}
