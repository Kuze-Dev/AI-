<?php

declare(strict_types=1);

namespace App\HttpTenantApi\Controllers\Payment;

use Domain\PaymentMethod\Models\PaymentMethod;
use Domain\Payments\Contracts\PaymentManagerInterface;
use Domain\Payments\Interfaces\HandlesWebhook;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Spatie\RouteAttributes\Attributes\Post;
use Throwable;

class PaymentWebhookController
{
    #[Post('/paymentwebhook/{paymentmethod}/{status}', name: 'payment-webhook')]
    public function handleWebhook(string $paymentmethod, string $status, Request $request): JsonResponse
    {
        try {
            $paymentMethod = PaymentMethod::where('slug', $paymentmethod)->first();

            if (! $paymentMethod) {
                abort(404, 'Payment method not found.');
            }

            $paymentProvider = app(PaymentManagerInterface::class)->driver($paymentMethod->gateway);

            if (! $paymentProvider instanceof HandlesWebhook) {
                abort(400, 'Webhook not supported for this provider.');
            }

            $paymentProvider->handleWebhook($request, $status);

            return response()->json([
                'message' => ucfirst($paymentmethod).' webhook processed.',
            ], 200);

        } catch (Throwable $e) {
            return response()->json([
                'message' => 'Webhook processing failed.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
