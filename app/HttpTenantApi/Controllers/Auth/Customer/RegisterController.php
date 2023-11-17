<?php

declare(strict_types=1);

namespace App\HttpTenantApi\Controllers\Auth\Customer;

use App\Features\Customer\CustomerBase;
use App\HttpTenantApi\Requests\Auth\Customer\CustomerRegisterRequest;
use App\HttpTenantApi\Resources\CustomerResource;
use App\Notifications\Customer\NewRegisterNotification;
use Domain\Auth\Actions\VerifyEmailAction;
use Domain\Customer\Actions\CreateCustomerAction;
use Domain\Customer\Actions\EditCustomerAction;
use Domain\Customer\Actions\SendForApprovalRegistrationAction;
use Domain\Customer\DataTransferObjects\CustomerData;
use Domain\Customer\Models\Customer;
use Domain\Tier\Models\Tier;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Spatie\RouteAttributes\Attributes\Middleware;
use Spatie\RouteAttributes\Attributes\Post;
use Throwable;

#[Middleware('feature.tenant:'.CustomerBase::class)]
class RegisterController
{
    /** @throws Throwable */
    #[Post('register', name: 'customer.register')]
    public function __invoke(CustomerRegisterRequest $request): CustomerResource
    {

        $customerTier = Tier::whereId($request->tier_id)->first();

        /** @var \Domain\Tier\Models\Tier $defaultTier */
        $defaultTier = Tier::whereName(config('domain.tier.default'))->first();

        if ($request->invited) {

            $validated = $request->validated();

            $customerModel = Customer::whereCuid($validated['invited'])->firstOrFail();
            
            $customer = DB::transaction(
                fn () => app(EditCustomerAction::class)
                    ->execute($customerModel, CustomerData::updateInvitedCustomer($validated))
            );

            app(VerifyEmailAction::class)->execute($customer);

        } else {
            $customer = DB::transaction(
                fn () => app(CreateCustomerAction::class)
                    ->execute(CustomerData::fromRegistrationRequest($request, $customerTier, $defaultTier))
            );
            if ($customerTier?->has_approval) {
                app(SendForApprovalRegistrationAction::class)->execute($customer);
            }
        }

        Notification::send($customer, new NewRegisterNotification($customer));

        return CustomerResource::make($customer);
    }
}
