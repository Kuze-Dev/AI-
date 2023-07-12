<?php

declare(strict_types=1);

namespace App\HttpTenantApi\Controllers\Auth\Customer;

use App\Features\ECommerce\ECommerceBase;
use App\HttpTenantApi\Requests\Auth\Customer\CustomerRegisterRequest;
use App\HttpTenantApi\Resources\CustomerResource;
use Domain\Customer\Actions\CustomerRegisterAction;
use Domain\Tier\Models\Tier;
use Illuminate\Support\Facades\DB;
use Spatie\RouteAttributes\Attributes\Middleware;
use Spatie\RouteAttributes\Attributes\Post;
use Throwable;

#[Middleware('feature.tenant:' . ECommerceBase::class)]
class RegisterController
{
    /** @throws Throwable */
    #[Post('register', name: 'customer.register')]
    public function __invoke(CustomerRegisterRequest $request): CustomerResource
    {
        /** @var \Domain\Tier\Models\Tier $tier */
        $tier = Tier::whereName(config('domain.tier.default'))->first();

        $customer = DB::transaction(
            fn () => app(CustomerRegisterAction::class)
                ->execute($request->toDTO($tier))
        );

        return CustomerResource::make($customer);
    }
}
