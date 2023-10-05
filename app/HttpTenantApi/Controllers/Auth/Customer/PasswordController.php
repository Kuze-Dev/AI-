<?php

declare(strict_types=1);

namespace App\HttpTenantApi\Controllers\Auth\Customer;

use App\Features\Customer\CustomerBase;
use App\Http\Controllers\Controller;
use Domain\Auth\Actions\ForgotPasswordAction;
use Domain\Auth\Actions\ResetPasswordAction;
use Domain\Auth\DataTransferObjects\ResetPasswordData;
use Domain\Customer\Actions\EditCustomerPasswordAction;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password as PasswordRule;
use Spatie\RouteAttributes\Attributes\Middleware;
use Spatie\RouteAttributes\Attributes\Post;
use Spatie\RouteAttributes\Attributes\Prefix;
use Spatie\RouteAttributes\Attributes\Put;
use Throwable;

#[
    Prefix('account/password'),
    Middleware('feature.tenant:' . CustomerBase::class)
]
class PasswordController extends Controller
{
    #[Post('email', name: 'password.request')]
    public function sendResetLinkEmail(Request $request): mixed
    {
        $email = $this->validate($request, [
            'email' => ['required', Rule::email()],
        ])['email'];

        $result = app(ForgotPasswordAction::class)
            ->execute($email, 'customer');

        $result->throw();

        return response()->json(['message' => $result->getMessage()]);
    }

    #[Post('reset', name: 'password.reset')]
    public function reset(Request $request): mixed
    {
        $validated = $this->validate($request, [
            'token' => 'required|string',
            'email' => ['required', Rule::email()],
            'password' => ['required', 'confirmed', PasswordRule::default()],
        ]);

        $result = app(ResetPasswordAction::class)->execute(
            new ResetPasswordData(...$validated),
            'customer'
        );

        $result->throw();

        return response()->json(['message' => $result->getMessage()]);
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

        return new JsonResponse(['message' => trans('Your password has been updated!')], 200);
    }
    #[Post('validate/token', name:'validate.token')]
    public function validateToken(Request $request):JsonResponse
    {
        $emailExists = DB::table('customer_password_resets')
        ->where('email', $request->email)
        ->exists();

        if ($emailExists) {
            // Email exists in the database
            return response()->json(['message' => 'Email exists'], 200);
        } else {
            // Email does not exist in the database
            return response()->json(['message' => 'Email does not exist'], 404);
        }
    }
}
