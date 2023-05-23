<?php

declare(strict_types=1);

use App\FilamentTenant\Resources\FormResource\Pages\CreateForm;
use Domain\Blueprint\Database\Factories\BlueprintFactory;
use Domain\Form\Models\Form;
use Domain\Form\Models\FormEmailNotification;
use Domain\Support\Captcha\CaptchaProvider;
use Filament\Facades\Filament;
use Spatie\LaravelSettings\Migrations\SettingsMigrator;

use function Pest\Laravel\assertDatabaseCount;
use function Pest\Livewire\livewire;

beforeEach(function () {
    testInTenantContext();
    Filament::setContext('filament-tenant');
    loginAsSuperAdmin();
});

it('can render page', function () {
    livewire(CreateForm::class)
        ->assertFormExists()
        ->assertOk();
});

it('can create form', function () {
    $blueprint = BlueprintFactory::new()
        ->withDummySchema()
        ->createOne();

    $this->assertDatabaseEmpty(Form::class);
    $this->assertDatabaseEmpty(FormEmailNotification::class);

    livewire(CreateForm::class)
        ->fillForm([
            'name' => fake()->sentence(2),
            'blueprint_id' => $blueprint->getKey(),
            'form_email_notifications' => [
                [
                    'to' => [fake()->safeEmail()],
                    'cc' => [fake()->safeEmail()],
                    'bcc' => [fake()->safeEmail()],
                    'sender' => fake()->safeEmail(),
                    'reply_to' => [fake()->safeEmail()],
                    'subject' => fake()->sentence(),
                    'template' => fake()->paragraphs(asText: true),
                ],
            ],
        ])
        ->call('create')
        ->assertHasNoFormErrors()
        ->assertOk();

    assertDatabaseCount(Form::class, 1);
    assertDatabaseCount(FormEmailNotification::class, 1);
});

it('can\'t toggle uses captcha if not set up', function () {
    livewire(CreateForm::class)
        ->assertFormFieldIsDisabled('uses_captcha');
});

it('can toggle uses captcha if set up', function () {
    resolve(SettingsMigrator::class)->update('form.provider', fn () => CaptchaProvider::GOOGLE_RECAPTCHA);

    livewire(CreateForm::class)
        ->assertFormFieldIsEnabled('uses_captcha');
});
