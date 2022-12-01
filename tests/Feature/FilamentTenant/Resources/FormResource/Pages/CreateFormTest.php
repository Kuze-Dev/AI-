<?php

declare(strict_types=1);

use App\FilamentTenant\Resources\FormResource\Pages\CreateForm;
use Domain\Blueprint\Database\Factories\BlueprintFactory;
use Domain\Form\Models\Form;
use Domain\Form\Models\FormEmailNotification;
use Filament\Facades\Filament;

use function Pest\Faker\faker;
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

it('can create page', function () {
    $blueprint = BlueprintFactory::new()
        ->withDummySchema()
        ->createOne();

    $this->assertDatabaseEmpty(Form::class);
    $this->assertDatabaseEmpty(FormEmailNotification::class);

    livewire(CreateForm::class)
        ->fillForm([
            'name' => faker()->sentence(2),
            'blueprint_id' => $blueprint->getKey(),
            'formEmailNotifications' => [
                [
                    'recipient' => faker()->safeEmail(),
                    'cc' => faker()->safeEmail(),
                    'bcc' => faker()->safeEmail(),
                    'reply_to' => faker()->safeEmail(),
                    'sender' => faker()->safeEmail(),
                    'template' => faker()->safeEmail(),
                ],
            ],
        ])
        ->call('create')
        ->assertOk()
        ->assertHasNoFormErrors();

    assertDatabaseCount(Form::class, 1);
    assertDatabaseCount(FormEmailNotification::class, 1);
});
