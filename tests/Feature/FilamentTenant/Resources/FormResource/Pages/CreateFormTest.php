<?php

declare(strict_types=1);

use App\FilamentTenant\Resources\FormResource\Pages\CreateForm;
use Domain\Blueprint\Database\Factories\BlueprintFactory;
use Domain\Form\Models\Form;
use Domain\Form\Models\FormEmailNotification;
use Support\Captcha\CaptchaProvider;
use Filament\Facades\Filament;
use Spatie\LaravelSettings\Migrations\SettingsMigrator;

use function Pest\Laravel\assertDatabaseHas;
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

    livewire(CreateForm::class)
        ->fillForm([
            'name' => 'Test',
            'blueprint_id' => $blueprint->getKey(),
            'form_email_notifications' => [
                [
                    'to' => ['test@user'],
                    'cc' => ['test@user'],
                    'bcc' => ['test@user'],
                    'sender_name' => 'test user',
                    'reply_to' => ['test@user'],
                    'subject' => 'Test Subject',
                    'template' => 'Some test template',
                ],
            ],
        ])
        ->call('create')
        ->assertHasNoFormErrors()
        ->assertOk();

    assertDatabaseHas(Form::class, [
        'name' => 'Test',
        'blueprint_id' => $blueprint->getKey(),
    ]);
    assertDatabaseHas(FormEmailNotification::class, [
        'to' => ['test@user'],
        'cc' => ['test@user'],
        'bcc' => ['test@user'],
        'sender_name' => 'test user',
        'reply_to' => ['test@user'],
        'subject' => 'Test Subject',
        'template' => 'Some test template',
    ]);
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
