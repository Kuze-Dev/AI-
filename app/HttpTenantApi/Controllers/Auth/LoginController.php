<?php

declare(strict_types=1);

namespace App\HttpTenantApi\Controllers\Auth;

use App\Http\Controllers\Controller;
use Domain\Customer\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Spatie\RouteAttributes\Attributes\Post;

class LoginController extends Controller
{
    #[Post(uri: 'login', name: 'login')]
    public function __invoke(Request $request): mixed
    {
        $validated = $this->validate($request, [
            'email' => ['required', Rule::email()],
            'password' => 'required|string',
        ]);

        $ok = Auth::guard('api')->attempt($validated);

        if ( ! $ok) {
            return response()->json([
                'Invalid credentials.',
            ], 401);
        }

        /** @var \Domain\Customer\Models\Customer $costumer */
        $costumer = Customer::whereEmail($validated['email'])
            ->first();

        return response([
            'token' => $costumer
                ->createToken(
                    name:'costumer auth',
                    expiresAt: now()->addHour(),
                )
                ->plainTextToken,
        ]);
    }
}
