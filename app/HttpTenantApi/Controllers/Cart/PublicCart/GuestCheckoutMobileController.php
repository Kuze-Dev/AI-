<?php

declare(strict_types=1);

namespace App\HttpTenantApi\Controllers\Cart\PublicCart;

use App\Features\ECommerce\AllowGuestOrder;
use App\Http\Controllers\Controller;
use App\HttpTenantApi\Resources\CartLineResource;
use Domain\Cart\Actions\PublicCart\GuestCartSummaryAction;
use Domain\Cart\DataTransferObjects\CartSummaryTaxData;
use Domain\Cart\DataTransferObjects\GuestCartSummaryShippingData;
use Domain\Cart\Requests\PublicCart\GuestCartMobileSummaryRequest;
use Domain\Shipment\API\AusPost\Exceptions\AusPostServiceNotFoundException;
use Domain\Shipment\API\USPS\Exceptions\USPSServiceNotFoundException;
use Spatie\RouteAttributes\Attributes\Middleware;
use Spatie\RouteAttributes\Attributes\Post;
use Throwable;

#[
    Middleware(['feature.tenant:'.AllowGuestOrder::class])
]
class GuestCheckoutMobileController extends Controller
{
    #[Post('/v2/guest/carts/summary', name: 'v2.guest.carts.summary')]
    public function summary(GuestCartMobileSummaryRequest $request): mixed
    {
        $sessionId = $request->bearerToken();

        if (is_null($sessionId)) {
            abort(403);
        }

        $validated = $request->validated();
        $discountCode = $validated['discount_code'] ?? null;
        $reference = $validated['reference'];

        $cartLines = $request->getCartLines();

        if ($cartLines->isNotEmpty()) {
            $expiredCartLines = $cartLines->where('checkout_expiration', '<=', now());

            // Check if there are expired cart lines
            if ($expiredCartLines->isNotEmpty()) {
                return response()->json([
                    'message' => 'Key has been expired, checkout again to revalidate your cart',
                ], 400);
            }
        }

        $country = $request->getCountry();
        $state = $request->getState();
        $discount = $request->getDiscount();
        $serviceId = $validated['service_id'] ?? null;

        try {
            $summary = app(GuestCartSummaryAction::class)->execute(
                $cartLines,
                new CartSummaryTaxData($country?->id, $state?->id),
                new GuestCartSummaryShippingData(
                    $request->toRecieverDTO(),
                    $request->getShippingAddress(),
                    $request->getShippingMethod()
                ),
                $discount,
                $serviceId ? $serviceId : null
            );

            $responseArray = [
                'summary' => [
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
                ],

                'cartLines' => CartLineResource::collection($cartLines),
                'reference' => $reference,
            ];

            if (! $discountCode) {
                unset($responseArray['summary']['discount']);
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
