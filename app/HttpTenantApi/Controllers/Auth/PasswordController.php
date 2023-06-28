<?php

declare(strict_types=1);

namespace App\HttpTenantApi\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Password as PasswordBroker;
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

        $response = PasswordBroker::broker('customer')->sendResetLink($validated);

        return $response === PasswordBroker::RESET_LINK_SENT
             ? new JsonResponse(['message' => trans($response)], 200)
             : throw ValidationException::withMessages([
                 'email' => [trans($response)],
             ]);
    }

    #[Post('reset', name: 'password.update')]
    public function reset(Request $request): mixed
    {
        return '';
    }
}
