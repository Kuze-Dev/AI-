<?php

declare(strict_types=1);

namespace App\HttpTenantApi\Controllers\Auth\Customer;

use App\Features\ECommerce\ECommerceBase;
use Domain\Auth\Actions\VerifyEmailAction;
use Domain\Customer\Models\Customer;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\Request;
use Spatie\RouteAttributes\Attributes\Get;
use Spatie\RouteAttributes\Attributes\Middleware;

#[Middleware(['feature.tenant:' . ECommerceBase::class])]
class VerifyEmailController
{
    #[Get('verify/{id}/{hash}', name: 'customer.verify')]
    public function __invoke(Request $request): mixed
    {
        $customer = app(Customer::class)->resolveRouteBinding($request->route('id'));

        if ($customer === null) {
            throw new AuthorizationException();
        }

        if ( ! hash_equals((string) $request->route('hash'), sha1($customer->getEmailForVerification()))) {
            throw new AuthorizationException();
        }

        return response([
            'message' => app(VerifyEmailAction::class)->execute($customer) === true
                    ? 'Email verified!'
                    : 'Email already verified.',
        ]);
    }
}
