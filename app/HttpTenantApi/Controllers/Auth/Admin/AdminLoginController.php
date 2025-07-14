<?php

declare(strict_types=1);

namespace App\HttpTenantApi\Controllers\Auth\Admin;

use App\Http\Controllers\Controller;
use Domain\Admin\Models\Admin;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Spatie\RouteAttributes\Attributes\Post;

class AdminLoginController extends Controller
{
    /** @throws \Illuminate\Auth\AuthenticationException */
    #[Post(uri: '/admin-login', name: 'admin-login')]
    public function __invoke(Request $request): mixed
    {
        $validated = $this->validate($request, [
            'email' => 'required|string',
            'password' => 'required|string',
        ]);

        if (! Auth::guard('admin-api')->attempt($validated)) {
            throw new AuthenticationException(trans('These credentials do not match our records.'));
        }
        /** @var Admin|null $admin */
        $admin = Admin::where('email', $validated['email'])
            ->whereNotNull('email_verified_at')
            ->first();

        if ($admin === null) {
            throw new AuthenticationException(trans('These credentials do not match our records.'));
        }
        
        $abilities = auth('admin-api')->user()->getPermissionNames()->toArray();

        return response([
            'token' => $admin
                ->createToken(
                    name: 'admin',
                    abilities: $abilities,
                    expiresAt: now()->addHour()
                )->plainTextToken,
        ]);
    }
}
