<?php

declare(strict_types=1);

namespace App\HttpTenantApi\Controllers\Auth\Customer;

use App\Features\ECommerce\ECommerceBase;
use App\Http\Controllers\Controller;
use Domain\Customer\Enums\RegisterStatus;
use Domain\Customer\Enums\Status;
use Domain\Customer\Models\Customer;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Spatie\RouteAttributes\Attributes\Middleware;
use Spatie\RouteAttributes\Attributes\Post;

#[Middleware('feature.tenant:' . ECommerceBase::class)]
class LoginController extends Controller
{
    /** @throws \Illuminate\Auth\AuthenticationException */
    #[Post(uri: 'login', name: 'login')]
    public function __invoke(Request $request): mixed
    {
        $validated = $this->validate($request, [
            'email' => ['required', Rule::email()],
            'password' => 'required|string',
        ]);

        if ( ! Auth::guard('api')->attempt($validated)) {
            throw new AuthenticationException(trans('Invalid credentials.'));
        }

        $customer = Customer::whereEmail($validated['email'])
            ->whereStatus(Status::ACTIVE)
            ->whereRegisterStatus(RegisterStatus::REGISTERED)
            ->first();

        if ($customer === null) {
            throw new AuthenticationException(trans('Invalid credentials.'));
        }

        return response([
            'token' => $customer
                ->createToken('customer')
                ->plainTextToken,
        ]);
    }
}
