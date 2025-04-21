<?php

declare(strict_types=1);

namespace Support\Captcha\Providers;

use Illuminate\Support\Facades\Http;

class GoogleRecaptchaProvider extends BaseProvider
{
    #[\Override]
    public function verify(string $token, ?string $ip = null): bool
    {
        $response = Http::asJson()
            ->post(
                'https://www.google.com/recaptcha/api/siteverify?'.http_build_query([
                    'secret' => $this->credentials['secret_key'],
                    'response' => $token,
                    'remoteip' => $ip,
                ])
            )
            ->throw();

        return $response['success'] ?? false;
    }
}
