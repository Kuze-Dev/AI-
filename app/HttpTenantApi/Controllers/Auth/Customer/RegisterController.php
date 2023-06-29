<?php

declare(strict_types=1);

namespace App\HttpTenantApi\Controllers\Auth\Customer;

use App\HttpTenantApi\Requests\Auth\Customer\CustomerRequest;
use App\HttpTenantApi\Resources\CustomerResource;
use Domain\Customer\Actions\CreateCustomerAction;
use Domain\Customer\DataTransferObjects\CustomerData;
use Illuminate\Support\Facades\DB;
use Spatie\RouteAttributes\Attributes\Post;
use Throwable;

class RegisterController
{
    /** @throws Throwable */
    #[Post('register')]
    public function __invoke(CustomerRequest $request): CustomerResource
    {
        $customer = DB::transaction(fn () => app(CreateCustomerAction::class)
            ->execute(CustomerData::fromArray($request->validated())));

        return CustomerResource::make($customer);
    }
}
