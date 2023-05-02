<?php

declare(strict_types=1);

namespace Domain\Support\Captcha\Providers;

use Illuminate\Support\Facades\Http;

class GoogleRecaptchaProvider extends BaseProvider
{
    public function verify(string $token, ?string $ip = null): bool
    {
        $response = Http::asJson()
            ->post(
                'https://www.google.com/recaptcha/api/siteverify',
                [
                    'secret' => $this->credentials['secret_key'],
                    'response' => $token,
                    'remoteip' => $ip,
                ]
            )
            ->throw();

        return $response['success'] ?? false;
    }
}
