<?php

declare(strict_types=1);

namespace App\HttpTenantApi\Controllers\Auth\Customer;

use App\Http\Controllers\Controller;
use Domain\Customer\Actions\CustomerPasswordResetAction;
use Domain\Customer\Actions\CustomerSendPasswordResetAction;
use Domain\Customer\Models\Customer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Password as PasswordBroker;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password as PasswordRule;
use Illuminate\Validation\ValidationException;
use Spatie\RouteAttributes\Attributes\Post;
use Spatie\RouteAttributes\Attributes\Prefix;

#[Prefix('password'), ]
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

    #[Post('reset', name: 'password.update')]
    public function reset(Request $request): mixed
    {
        $validated = $this->validate($request, [
            'token' => 'required|string',
            'email' => ['required', Rule::email()],
            'password' => PasswordRule::required(),
        ]);

        $response = PasswordBroker::broker('customer')
            ->reset(
                $validated,
                function (Customer $customer, string $password): void {
                    DB::transaction(fn () => app(CustomerPasswordResetAction::class)
                        ->execute($customer, $password));
                }
            );

        return $response == PasswordBroker::PASSWORD_RESET
            ? new JsonResponse(['message' => trans($response)], 200)
            : throw ValidationException::withMessages([
                'email' => [trans($response)],
            ]);
    }
}
