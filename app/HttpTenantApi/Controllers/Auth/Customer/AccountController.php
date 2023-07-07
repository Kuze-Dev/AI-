<?php

declare(strict_types=1);

namespace App\HttpTenantApi\Controllers\Auth\Customer;

use App\Features\ECommerce\ECommerceBase;
use App\Http\Controllers\Controller;
use App\HttpTenantApi\Resources\CustomerResource;
use Domain\Customer\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Spatie\RouteAttributes\Attributes\Get;
use Spatie\RouteAttributes\Attributes\Middleware;
use Spatie\RouteAttributes\Attributes\Prefix;
use Spatie\RouteAttributes\Attributes\Put;

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

    #[Put('update', name: 'account.update')]
    public function update(Request $request): CustomerResource
    {
        $validated = $this->validate($request, [
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => [
                'required',
                Rule::unique(Customer::class)->ignoreModel(Auth::user()),
                Rule::email(),
                'max:255',
            ],
            'mobile' => 'required|string|max:255',
            'birth_date' => 'required|date',
            'password' => ['required', 'confirmed', Password::default()],
        ]);

        return CustomerResource::make(Auth::user());
    }
}
