<?php

declare(strict_types=1);

namespace Support\Captcha\Providers;

use Illuminate\Support\Facades\Http;

class CloudflareTurnstileProvider extends BaseProvider
{
    #[\Override]
    public function verify(string $token, ?string $ip = null): bool
    {
        $response = Http::asForm() // important: sends application/x-www-form-urlencoded
            ->post('https://challenges.cloudflare.com/turnstile/v0/siteverify', [
                'secret' => $this->credentials['secret_key'],
                'response' => $token,
                'remoteip' => $ip,
            ])
            ->throw();

        return $response['success'] ?? false;
    }
}
