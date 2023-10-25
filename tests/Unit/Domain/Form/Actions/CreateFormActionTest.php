<?php

declare(strict_types=1);

use Domain\Blueprint\Database\Factories\BlueprintFactory;
use Domain\Form\Actions\CreateFormAction;
use Domain\Form\DataTransferObjects\FormData;
use Domain\Form\Models\Form;
use Domain\Form\Models\FormEmailNotification;
use Domain\Site\Database\Factories\SiteFactory;

use function Pest\Laravel\assertDatabaseHas;

beforeEach(fn () => testInTenantContext());

it('store', function () {
    $blueprint = BlueprintFactory::new()
        ->withDummySchema()
        ->createOne();
    $site = SiteFactory::new()
        ->createOne();

    $form = app(CreateFormAction::class)
        ->execute(FormData::fromArray([
            'blueprint_id' => $blueprint->getKey(),
            'name' => 'Test',
            'store_submission' => false,
            'form_email_notifications' => [
                [
                    'to' => ['test@user'],
                    'sender' => 'test@user',
                    'sender_name' => 'test user',
                    'subject' => 'Foo Subject',
                    'template' => 'Foo Template',
                    'has_attachments' => false,
                ],
            ],
            'sites' => [$site->id],
        ]));

    assertDatabaseHas(Form::class, [
        'blueprint_id' => $blueprint->getKey(),
        'name' => 'Test',
        'store_submission' => false,
    ]);
    assertDatabaseHas(FormEmailNotification::class, [
        'to' => 'test@user',
        'sender' => 'test@user',
        'sender_name' => 'test user',
        'subject' => 'Foo Subject',
        'template' => 'Foo Template',
    ]);

    expect($form->sites->pluck('id'))->toContain($site->id);
});
