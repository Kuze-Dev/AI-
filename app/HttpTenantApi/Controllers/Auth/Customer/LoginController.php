<?php

declare(strict_types=1);

namespace App\HttpTenantApi\Controllers\Auth\Customer;

use App\Features\Customer\CustomerBase;
use App\Http\Controllers\Controller;
use Domain\Customer\Enums\RegisterStatus;
use Domain\Customer\Enums\Status;
use Domain\Customer\Models\Customer;
use Domain\Tenant\Support\ApiAbilitties;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Spatie\RouteAttributes\Attributes\Middleware;
use Spatie\RouteAttributes\Attributes\Post;

#[Middleware('feature.tenant:'.CustomerBase::class)]
class LoginController extends Controller
{
    /** @throws \Illuminate\Auth\AuthenticationException */
    #[Post(uri: 'login', name: 'login')]
    public function __invoke(Request $request): mixed
    {
        $validated = $this->validate($request, [
            'email' => [
                'required_if:email,null',
                'nullable',
                'email',
            ],
            'username' => [
                'required_if:username,null',
            ],
            'password' => 'required|string',
            'login' => [
                'required_if:login,null',
            ],
        ]);

        if (array_key_exists('login', $validated)) {

            $loginType = filter_var($validated['login'], FILTER_VALIDATE_EMAIL) ? 'email' : 'username';

            $data = [
                $loginType => $validated['login'],
                'password' => $validated['password'],
            ];

        } else {

            if (array_key_exists('email', $validated)) {
                $loginType = 'email';
            } else {
                $loginType = 'username';
            }

            $data = $validated;
        }

        if (! Auth::guard('api')->attempt($data)) {
            abort(401, trans('These credentials do not match our records.'));
        }

        $customer = Customer::where(function ($q) use ($validated, $loginType) {
            if (array_key_exists('login', $validated)) {
                return $q->where($loginType, $validated['login']);
            }

            return $q->where($loginType, $validated[$loginType]);
        })
            ->whereStatus(Status::ACTIVE)
            ->whereRegisterStatus(RegisterStatus::REGISTERED)
            ->first();

        if ($customer === null) {
            abort(401, trans('These credentials do not match our records.'));
        }

        return response([
            'token' => $customer
                ->createToken(
                    name: 'customer',
                    abilities: ApiAbilitties::cmsCustomerAbilities(),
                    expiresAt: now()->addHour()
                )
                ->plainTextToken,
        ]);
    }
}
