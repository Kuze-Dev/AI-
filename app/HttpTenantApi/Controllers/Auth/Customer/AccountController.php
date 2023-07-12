<?php

declare(strict_types=1);

namespace App\HttpTenantApi\Controllers\Auth\Customer;

use App\Features\ECommerce\ECommerceBase;
use App\Http\Controllers\Controller;
use App\HttpTenantApi\Resources\CustomerResource;
use Domain\Customer\Actions\EditCustomerAction;
use Domain\Customer\DataTransferObjects\CustomerData;
use Domain\Customer\Enums\Gender;
use Domain\Customer\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Spatie\RouteAttributes\Attributes\Get;
use Spatie\RouteAttributes\Attributes\Middleware;
use Spatie\RouteAttributes\Attributes\Prefix;
use Spatie\RouteAttributes\Attributes\Put;
use Throwable;

#[
    Prefix('account'),
    Middleware(['auth:sanctum', 'feature.tenant:' . ECommerceBase::class])]
class AccountController extends Controller
{
    #[Get('/', name: 'account')]
    public function show(): CustomerResource
    {
        return CustomerResource::make(Auth::user());
    }

    /** @throws Throwable */
    #[Put('update', name: 'account.update')]
    public function update(Request $request): CustomerResource
    {
        /** @var \Domain\Customer\Models\Customer $customer */
        $customer = Auth::user();

        $validated = $this->validate($request, [
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => [
                'required',
                Rule::unique(Customer::class)->ignoreModel($customer),
                Rule::email(),
                'max:255',
            ],
            'gender' => ['required', Rule::enum(Gender::class)],
            'mobile' => 'required|string|max:255',
            'birth_date' => 'required|date',
        ]);

        $customer = DB::transaction(
            fn () => app(EditCustomerAction::class)
                ->execute($customer, new CustomerData(
                    first_name: $validated['first_name'],
                    last_name: $validated['last_name'],
                    mobile: $validated['mobile'],
                    gender: Gender::from($validated['gender']),
                    birth_date: now()->parse($validated['birth_date']),
                    email: $validated['email'],
                ))
        );

        return CustomerResource::make($customer);
    }
}
