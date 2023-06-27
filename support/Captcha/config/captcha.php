<?php

return [

    /**
     * The captcha service to be used.
     *
     * Available options are CaptchaProvider::GOOGLE_RECAPTCHA, CaptchaProvider::CLOUDFLARE_TURNSTILE
     */
    'provider' => null,

    'credentials' => [
        'site_key' => env('CATPCHA_SITE_KEY'),
        'secret_key' => env('CATPCHA_SECRET_KEY'),
    ],

];
