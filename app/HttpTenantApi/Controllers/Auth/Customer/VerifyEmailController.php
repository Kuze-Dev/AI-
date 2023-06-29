<?php

declare(strict_types=1);

namespace App\HttpTenantApi\Controllers\Auth\Customer;

use App\Features\ECommerce\ECommerceBase;
use App\Filament\Requests\VerifyEmailRequest;
use Domain\Auth\Actions\VerifyEmailAction;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Spatie\RouteAttributes\Attributes\Get;
use Spatie\RouteAttributes\Attributes\Middleware;

#[Middleware(['auth:sanctum', 'feature.tenant:' . ECommerceBase::class])]
class VerifyEmailController
{
    #[Get('verify', name: 'customer.verify')]
    public function __invoke(VerifyEmailRequest $request): mixed
    {
        $user = $request->user();

        if ( ! $user instanceof MustVerifyEmail) {
            throw new AuthorizationException();
        }

        return response([
            'message' => app(VerifyEmailAction::class)->execute($user)
                    ? 'Email Verified!'
                    : 'Failed to verify your email.',
        ]);
    }
}
