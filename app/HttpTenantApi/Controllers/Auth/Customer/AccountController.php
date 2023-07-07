<?php

declare(strict_types=1);

namespace App\HttpTenantApi\Controllers\Auth\Customer;

use App\Features\ECommerce\ECommerceBase;
use App\Http\Controllers\Controller;
use App\HttpTenantApi\Resources\CustomerResource;
use Domain\Customer\Actions\EditCustomerAction;
use Domain\Customer\DataTransferObjects\CustomerData;
use Domain\Customer\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
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
            'mobile' => 'required|string|max:255',
            'birth_date' => 'required|date',
            'password' => ['required', 'confirmed', Password::default()],
        ]);

        $customer = DB::transaction(
            fn () => app(EditCustomerAction::class)
                ->execute($customer, new CustomerData(
                    first_name: $validated['first_name'],
                    last_name: $validated['last_name'],
                    mobile: $validated['mobile'],
                    birth_date: now()->parse($validated['birth_date']),
                    email: $validated['email'],
                    password: $validated['password']
                ))
        );

        return CustomerResource::make($customer);
    }
}
