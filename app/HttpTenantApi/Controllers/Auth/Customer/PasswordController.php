<?php

declare(strict_types=1);

namespace App\HttpTenantApi\Controllers\Auth\Customer;

use App\Features\ECommerce\ECommerceBase;
use App\Http\Controllers\Controller;
use Domain\Customer\Actions\CustomerPasswordResetAction;
use Domain\Customer\Actions\CustomerSendPasswordResetAction;
use Domain\Customer\Actions\EditCustomerPasswordAction;
use Domain\Customer\Models\Customer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password as PasswordBroker;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password as PasswordRule;
use Illuminate\Validation\ValidationException;
use Spatie\RouteAttributes\Attributes\Middleware;
use Spatie\RouteAttributes\Attributes\Post;
use Spatie\RouteAttributes\Attributes\Prefix;
use Spatie\RouteAttributes\Attributes\Put;
use Throwable;

#[
    Prefix('password'),
    Middleware('feature.tenant:' . ECommerceBase::class)
]
class PasswordController extends Controller
{
    #[Post('email', name: 'password.request')]
    public function sendResetLinkEmail(Request $request): mixed
    {
        $validated = $this->validate($request, [
            'email' => ['required', Rule::email()],
        ]);

        $response = PasswordBroker::broker('customer')
            ->sendResetLink($validated, function (Customer $customer, string $token): void {
                DB::transaction(fn () => app(CustomerSendPasswordResetAction::class)
                    ->execute($customer, $token));
            });

        return $response === PasswordBroker::RESET_LINK_SENT
             ? new JsonResponse(['message' => trans($response)], 200)
             : throw ValidationException::withMessages([
                 'email' => [trans($response)],
             ]);
    }

    #[Post('reset', name: 'password.reset')]
    public function reset(Request $request): mixed
    {
        $validated = $this->validate($request, [
            'token' => 'required|string',
            'email' => ['required', Rule::email()],
            'password' => ['required', 'confirmed', PasswordRule::default()],
        ]);

        $response = PasswordBroker::broker('customer')
            ->reset(
                $validated,
                function (Customer $customer, string $password): void {
                    DB::transaction(fn () => app(CustomerPasswordResetAction::class)
                        ->execute($customer, $password));
                }
            );

        return new JsonResponse(
            ['message' => trans($response)],
            $response == PasswordBroker::PASSWORD_RESET ? 200 : 422
        );
    }

    /** @throws Throwable */
    #[Put('/', name: 'password.update', middleware: 'auth:sanctum')]
    public function update(Request $request): JsonResponse
    {
        /** @var \Domain\Customer\Models\Customer $customer */
        $customer = Auth::user();

        $validated = $this->validate($request, [
            'current_password' => [
                'required',
                'string',
                function ($attributes, $value, $failed) use ($customer) {
                    if( ! Hash::check($value, $customer->password)) {
                        $failed(trans('Invalid :attribute.'));
                    }
                },
            ],
            'password' => ['required', 'confirmed', PasswordRule::default()],
        ]);

        DB::transaction(
            fn () => app(EditCustomerPasswordAction::class)
                ->execute($customer, $validated['password'])
        );

        return new JsonResponse(['message' => trans('Password updated.')], 200);
    }
}
