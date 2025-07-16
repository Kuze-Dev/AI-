<?php

declare(strict_types=1);

namespace App\HttpTenantApi\Controllers\Auth\Cms;

use App\Http\Controllers\Controller;
use Domain\Tenant\Models\TenantApiKey;
use Illuminate\Http\Request;
use Spatie\RouteAttributes\Attributes\Post;
use Symfony\Component\HttpFoundation\Response;

class ApikeyController extends Controller
{
    /** @throws \Illuminate\Auth\AuthenticationException */
    #[Post(uri: '/auth/token', name: 'auth.token')]
    public function __invoke(Request $request): mixed
    {

        if (! config('custom.strict_api')) {
            return response()->json(['message' => Response::$statusTexts[Response::HTTP_FORBIDDEN]], Response::HTTP_FORBIDDEN);
        }

        $validated = $this->validate($request, [
            'api_key' => 'required|string',
            'secret_key' => 'required|string',
        ]);

        try {

            /** @var TenantApiKey $tenantApiKey */
            $tenantApiKey = TenantApiKey::where('api_key', $validated['api_key'])
                ->where('secret_key', $validated['secret_key'])
                ->firstorfail();

            $tenantApiKey->last_used_at = now();
            $tenantApiKey->save();

            // revoke all other tokens for this tenantApiKey
            $tenantApiKey->tokens()->each(function ($token) {
                $token->delete();
            });

            return response([
                'token' => $tenantApiKey
                    ->createToken(
                        name: $tenantApiKey->app_name,
                        abilities: $tenantApiKey->abilities ?? [],
                        expiresAt: now()->addHour()
                    )->plainTextToken,
            ]);

        } catch (\Throwable $th) {

            if ($th instanceof \Illuminate\Database\Eloquent\ModelNotFoundException) {
                return response()->json(['message' => Response::$statusTexts[Response::HTTP_UNAUTHORIZED]], Response::HTTP_UNAUTHORIZED);
            }

            throw $th;
        }

    }
}
