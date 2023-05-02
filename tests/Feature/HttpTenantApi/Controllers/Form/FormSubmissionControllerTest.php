<?php

declare(strict_types=1);

use Domain\Form\Database\Factories\FormFactory;

use Domain\Form\Models\FormSubmission;
use Domain\Support\Captcha\CaptchaProvider;
use Domain\Support\Captcha\Facades\Captcha;
use Spatie\LaravelSettings\Migrations\SettingsMigrator;

use function Pest\Laravel\assertDatabaseCount;
use function Pest\Laravel\assertDatabaseEmpty;
use function Pest\Laravel\postJson;

beforeEach(fn () => testInTenantContext());

it('can submit form', function () {
    $form = FormFactory::new()
        ->withDummyBlueprint()
        ->storeSubmission()
        ->createOne();

    assertDatabaseEmpty(FormSubmission::class);

    postJson(
        'api/forms/' . $form->getRouteKey() . '/submissions',
        [fake()->word() => fake()->sentence(3)]
    )
        ->assertCreated()
        ->assertValid();

    assertDatabaseCount(FormSubmission::class, 1);
});

it('can submit form with captcha', function () {
    $form = FormFactory::new()
        ->withDummyBlueprint()
        ->storeSubmission()
        ->usesCaptcha()
        ->createOne();

    resolve(SettingsMigrator::class)->update('form.provider', fn () => CaptchaProvider::GOOGLE_RECAPTCHA);

    assertDatabaseEmpty(FormSubmission::class);

    Captcha::shouldReceive('verify')
        ->once()
        ->andReturn(true);

    postJson(
        'api/forms/' . $form->getRouteKey() . '/submissions',
        [
            fake()->word() => fake()->sentence(3),
            'captcha_token' => 'some-token'
        ]
    )
        ->assertCreated()
        ->assertValid();

    assertDatabaseCount(FormSubmission::class, 1);
});
