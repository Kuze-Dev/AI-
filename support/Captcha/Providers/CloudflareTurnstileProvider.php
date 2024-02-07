<?php

declare(strict_types=1);

namespace Support\Captcha\Providers;

use Illuminate\Support\Facades\Http;

class CloudflareTurnstileProvider extends BaseProvider
{
    public function verify(string $token, ?string $ip = null): bool
    {
        $response = Http::asJson()
            ->post(
                'https://challenges.cloudflare.com/turnstile/v0/siteverify?'.http_build_query([
                    'secret' => $this->credentials['secret_key'],
                    'response' => $token,
                    'remoteip' => $ip,
                ])
            )
            ->throw();

        return $response['success'] ?? false;
    }
}
