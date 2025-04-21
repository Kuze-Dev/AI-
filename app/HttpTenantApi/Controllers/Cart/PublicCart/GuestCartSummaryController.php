<?php

declare(strict_types=1);

namespace App\HttpTenantApi\Controllers\Cart\PublicCart;

use App\Features\ECommerce\AllowGuestOrder;
use App\Http\Controllers\Controller;
use Domain\Cart\Actions\PublicCart\GuestCartSummaryAction;
use Domain\Cart\Actions\SanitizeCartSummaryAction;
use Domain\Cart\DataTransferObjects\CartSummaryTaxData;
use Domain\Cart\DataTransferObjects\GuestCartSummaryShippingData;
use Domain\Cart\Models\CartLine;
use Domain\Cart\Requests\PublicCart\GuestCartSummaryRequest;
use Domain\Shipment\API\AusPost\Exceptions\AusPostServiceNotFoundException;
use Domain\Shipment\API\USPS\Exceptions\USPSServiceNotFoundException;
use Illuminate\Http\Request;
use Spatie\RouteAttributes\Attributes\Get;
use Spatie\RouteAttributes\Attributes\Middleware;
use Spatie\RouteAttributes\Attributes\Post;
use Throwable;

#[
    Middleware(['feature.tenant:'.AllowGuestOrder::class])
]
class GuestCartSummaryController extends Controller
{
    #[Get('guest/carts/count', name: 'guest.carts.count')]
    public function count(Request $request): mixed
    {
        $sessionId = $request->bearerToken();

        if (is_null($sessionId)) {
            abort(403);
        }

        $cartLines = CartLine::with('purchasable')
            ->whereHas('cart', function ($query) use ($sessionId) {
                $query->where('session_id', $sessionId);
            })
            ->whereNull('checked_out_at')
            ->get();

        if ($cartLines->count()) {
            app(SanitizeCartSummaryAction::class)->sanitizeGuest($cartLines);
        }

        return response()->json(['cartCount' => $cartLines->count()], 200);
    }

    #[Post('guest/carts/summary', name: 'guest.carts.summary')]
    public function summary(GuestCartSummaryRequest $request): mixed
    {
        $sessionId = $request->bearerToken();

        if (is_null($sessionId)) {
            abort(403);
        }

        $validated = $request->validated();
        $discountCode = $validated['discount_code'] ?? null;

        $cartLines = $request->getCartLines();

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
