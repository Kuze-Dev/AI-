<?php

declare(strict_types=1);

namespace App\HttpTenantApi\Controllers\Auth\Customer;

use App\Features\Customer\CustomerBase;
use App\HttpTenantApi\Requests\Auth\Customer\CustomerRegisterRequest;
use App\HttpTenantApi\Resources\CustomerResource;
use App\Notifications\Customer\NewRegisterNotification;
use Domain\Customer\Actions\CreateCustomerAction;
use Domain\Customer\DataTransferObjects\CustomerData;
use Domain\Tier\Models\Tier;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Spatie\RouteAttributes\Attributes\Middleware;
use Spatie\RouteAttributes\Attributes\Post;
use Throwable;

#[Middleware('feature.tenant:' . CustomerBase::class)]
class RegisterController
{
    /** @throws Throwable */
    #[Post('register', name: 'customer.register')]
    public function __invoke(CustomerRegisterRequest $request): CustomerResource
    {
        /** @var \Domain\Tier\Models\Tier $tier */
        $tier = Tier::whereName(config('domain.tier.default'))->first();

        $customer = DB::transaction(
            fn () => app(CreateCustomerAction::class)
                ->execute(CustomerData::fromRegistrationRequest($tier, $request))
        );

        Notification::send($customer, new NewRegisterNotification($customer));

        return CustomerResource::make($customer);
    }
}
