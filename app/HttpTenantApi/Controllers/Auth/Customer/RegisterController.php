<?php

declare(strict_types=1);

namespace App\HttpTenantApi\Controllers\Auth\Customer;

use App\Features\ECommerce\ECommerceBase;
use App\HttpTenantApi\Requests\Auth\Customer\CustomerRequest;
use App\HttpTenantApi\Resources\CustomerResource;
use Domain\Customer\Actions\CreateCustomerAction;
use Domain\Customer\DataTransferObjects\CustomerData;
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
    public function __invoke(CustomerRequest $request): CustomerResource
    {
        $tierId = Tier::whereName(config('domain.tier.default'))->first()->getKey();

        $customer = DB::transaction(
            fn () => app(CreateCustomerAction::class)
                ->execute(CustomerData::fromArray($request->validated() + ['tier_id' => $tierId]))
        );

        return CustomerResource::make($customer);
    }
}
