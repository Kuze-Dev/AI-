<?php

declare(strict_types=1);

namespace App\HttpTenantApi\Controllers\Auth\Cms;

use App\Http\Controllers\Controller;
use Domain\Admin\Models\Admin;
use Domain\Tenant\Models\TenantApiKey;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Spatie\RouteAttributes\Attributes\Post;

class ApikeyController extends Controller
{
    /** @throws \Illuminate\Auth\AuthenticationException */
    #[Post(uri: '/auth/token', name: 'auth.token')]
    public function __invoke(Request $request): mixed
    {
        $validated = $this->validate($request, [
            'api_key' => 'required|string',
            'secret_key' => 'required|string',
        ]);


        try {
            $tenantApiKey = TenantApiKey::where('api_key', $validated['api_key'])
            ->where('secret_key', $validated['secret_key'])
            ->firstorfail();

        return response([
            'token' => $tenantApiKey
                ->createToken(
                    name: 'admin',
                    abilities: $tenantApiKey->abilities ?? [],
                    expiresAt: now()->addHour()
                )->plainTextToken,
        ]);
        } catch (\Throwable $th) {
            
            if ($th instanceof \Illuminate\Database\Eloquent\ModelNotFoundException) {
                return response()->json(['error' => 'Unauthorized'], 401);
            }

            throw $th;
        }
      
    }
}
