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
    protected CreateCustomerAction $createCustomerAction;

    protected EditCustomerAction $editCustomerAction;

    protected VerifyEmailAction $verifyEmailAction;

    protected SendForApprovalRegistrationAction $sendForApprovalRegistrationAction;

    public function __construct(CreateCustomerAction $createCustomerAction,
        EditCustomerAction $editCustomerAction,
        VerifyEmailAction $verifyEmailAction,
        SendForApprovalRegistrationAction $sendForApprovalRegistrationAction)
    {
        $this->createCustomerAction = $createCustomerAction;
        $this->editCustomerAction = $editCustomerAction;
        $this->verifyEmailAction = $verifyEmailAction;
        $this->sendForApprovalRegistrationAction = $sendForApprovalRegistrationAction;
    }

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
                fn () => $this->editCustomerAction->execute($customerModel, CustomerData::updateInvitedCustomer($validated))
            );

            $this->verifyEmailAction->execute($customer);

        } else {
            $customer = DB::transaction(
                fn () => $this->createCustomerAction
                    ->execute(
                        CustomerData::fromRegistrationRequest(
                            request: $request,
                            customerTier: $customerTier,
                            defaultTier: $defaultTier
                        )
                    )
            );
            if ($customerTier?->has_approval) {
                $this->sendForApprovalRegistrationAction->execute($customer);
            }
        }

        Notification::send($customer, new NewRegisterNotification($customer));

        return CustomerResource::make($customer);
    }
}
