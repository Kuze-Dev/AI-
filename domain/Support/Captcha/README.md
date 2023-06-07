# Captcha

Protect your forms from spam and abuse.

> This uses Google's reCAPTCHA and/or Cloudflare's Turnstile for captcha verification.

## Setup

Start by modifying the config and setting a `CaptchaProvider`.

> You can choose between `CaptchaProvider::GOOGLE_RECAPTCHA` and `CaptchaProvider::CLOUDFLARE_TURNSTILE`.

Then in your `.env` add your `CATPCHA_SITE_KEY`, `CATPCHA_SECRET_KEY`.

## Usage

You can use the `CaptchaRule` to validate the captcha tokens in your form.

```php
Validator::make(
    [
        'captcha_token' => 'some-token'
    ],
    [
        'captcha_token' => ['required', new CaptchaRule()],
    ]
)
```

You can also pass the client's IP to ensure that we're verifying with correct visitor.

```php
new CaptchaRule($request->ip())
```

## Advance Usage

You may dynamically set the provider:

```php
CaptchaManager::resolveProviderUsing(function () {
    return CaptchaProvider::GOOGLE_RECAPTCHA;
});

CaptchaManager::resolveCredentialsUsing(function () {
    return [
        'site_key' => 'some-site-key',
        'secret_key' => 'some-site-secret',
    ];
});
```
