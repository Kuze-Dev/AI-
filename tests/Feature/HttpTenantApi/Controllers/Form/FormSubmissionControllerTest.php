<?php

declare(strict_types=1);

use Domain\Blueprint\Database\Factories\BlueprintFactory;
use Domain\Blueprint\Enums\FieldType;
use Domain\Form\Database\Factories\FormFactory;
use Domain\Form\Models\FormSubmission;
use Spatie\LaravelSettings\Migrations\SettingsMigrator;
use Support\Captcha\CaptchaProvider;
use Support\Captcha\Facades\Captcha;

use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\postJson;

beforeEach(fn () => testInTenantContext());

it('can submit form', function () {
    $form = FormFactory::new()
        ->for(
            BlueprintFactory::new()
                ->addSchemaSection(['title' => 'Main'])
                ->addSchemaField(['title' => 'Name', 'type' => FieldType::TEXT, 'rules' => ['required']])
        )
        ->storeSubmission()
        ->createOne();

    postJson("api/forms/{$form->getRouteKey()}/submissions", ['main' => ['name' => 'foo']])
        ->assertCreated()
        ->assertValid();

    assertDatabaseHas(
        FormSubmission::class,
        ['data' => json_encode(['main' => ['name' => 'foo']])]
    );
});

it('can submit form with captcha', function () {
    $form = FormFactory::new()
        ->for(
            BlueprintFactory::new()
                ->addSchemaSection(['title' => 'Main'])
                ->addSchemaField(['title' => 'Name', 'type' => FieldType::TEXT, 'rules' => ['required']])
        )
        ->storeSubmission()
        ->usesCaptcha()
        ->createOne();

    resolve(SettingsMigrator::class)->update('form.provider', fn () => CaptchaProvider::GOOGLE_RECAPTCHA);

    Captcha::shouldReceive('verify')
        ->once()
        ->andReturn(true);

    postJson(
        "api/forms/{$form->getRouteKey()}/submissions",
        [
            'main' => ['name' => 'foo'],
            'captcha_token' => 'some-token',
        ]
    )
        ->assertCreated()
        ->assertValid();

    assertDatabaseHas(
        FormSubmission::class,
        ['data' => json_encode(['main' => ['name' => 'foo']])]
    );
});
