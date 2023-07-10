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
    #[Get('verify/{customer}/{hash}', name: 'customer.verify')]
    public function __invoke(Request $request, Customer $customer): mixed
    {
        if ( ! hash_equals($request->route('hash'), sha1($customer->getEmailForVerification()))) {
            throw new AuthorizationException();
        }

        return response([
            'message' => app(VerifyEmailAction::class)->execute($customer) === true
                    ? 'Email verified!'
                    : 'Email already verified.',
        ]);
    }
}
