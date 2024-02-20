<?php

declare(strict_types=1);

use App\Features\CMS\SitesManagement;
use App\FilamentTenant\Resources\FormResource\Pages\CreateForm;
use Domain\Blueprint\Database\Factories\BlueprintFactory;
use Domain\Form\Database\Factories\FormFactory;
use Domain\Form\Models\Form;
use Domain\Form\Models\FormEmailNotification;
use Domain\Internationalization\Database\Factories\LocaleFactory;
use Domain\Site\Database\Factories\SiteFactory;
use Spatie\LaravelSettings\Migrations\SettingsMigrator;
use Support\Captcha\CaptchaProvider;

use function Pest\Laravel\assertDatabaseCount;
use function Pest\Laravel\assertDatabaseHas;
use function Pest\Livewire\livewire;

beforeEach(function () {
    testInTenantContext();
    loginAsSuperAdmin();
    LocaleFactory::createDefault();
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
                    'sender' => 'test@user',
                    'sender_name' => 'test user',
                    'reply_to' => ['test@user'],
                    'subject' => 'Test Subject',
                    'template' => 'Some test template',
                    'has_attachments' => false,
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
        'sender' => 'test@user',
        'sender_name' => 'test user',
        'reply_to' => ['test@user'],
        'subject' => 'Test Subject',
        'template' => 'Some test template',
        'has_attachments' => false,
    ]);
});

it('can create form with same name', function () {

    activateFeatures(SitesManagement::class);

    $form = FormFactory::new()
        ->withDummyBlueprint()
        ->storeSubmission()
        ->createOne();

    SiteFactory::new()->count(2)->create();

    $form->sites()->sync([1]);

    livewire(CreateForm::class)
        ->fillForm([
            'name' => $form->name,
            'blueprint_id' => $form->blueprint_id,
            'sites' => [2],
        ])
        ->call('create')
        ->assertHasNoFormErrors()
        ->assertOk();

    assertDatabaseCount(Form::class, 2);

});

it('cannot create form with same name in same microsite', function () {

    activateFeatures(SitesManagement::class);

    $form = FormFactory::new()
        ->withDummyBlueprint()
        ->storeSubmission()
        ->createOne();

    SiteFactory::new()->create();

    $form->sites()->sync([1]);

    livewire(CreateForm::class)
        ->fillForm([
            'name' => $form->name,
            'blueprint_id' => $form->blueprint_id,
            'sites' => [1],
        ])
        ->call('create');

    assertDatabaseCount(Form::class, 1);

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
