<?php

declare(strict_types=1);

use Domain\Blueprint\Database\Factories\BlueprintFactory;
use Domain\Form\Actions\CreateFormAction;
use Domain\Form\DataTransferObjects\FormData;
use Domain\Form\DataTransferObjects\FormEmailNotificationData;
use Domain\Form\Models\Form;
use Domain\Form\Models\FormEmailNotification;
use Illuminate\Support\Str;

use function Pest\Faker\faker;
use function Pest\Laravel\assertDatabaseCount;
use function Pest\Laravel\assertDatabaseHas;

beforeEach(fn () => testInTenantContext());

it('store', function () {
    $blueprint = BlueprintFactory::new()
        ->withDummySchema()
        ->createOne();

    $name = faker()->sentence(2);
    $storeSubmission = faker()->boolean();
    $slug = faker()->boolean() ? Str::slug(faker()->sentence(2)) : null;

    // 2 rows to make sure it iterate properly
    $emailNotifications = [
        [
            'to' => faker()->safeEmail(),
            'cc' => faker()->boolean() ? faker()->safeEmail() : null,
            'bcc' => faker()->boolean() ? faker()->safeEmail() : null,
            'sender' => faker()->safeEmail(),
            'reply_to' => faker()->boolean() ? faker()->safeEmail() : null,
            'subject' => faker()->sentence(),
            'template' => faker()->safeEmail(),
        ],
        [
            'to' => faker()->safeEmail(),
            'cc' => faker()->boolean() ? faker()->safeEmail() : null,
            'bcc' => faker()->boolean() ? faker()->safeEmail() : null,
            'sender' => faker()->safeEmail(),
            'reply_to' => faker()->boolean() ? faker()->safeEmail() : null,
            'subject' => faker()->sentence(),
            'template' => faker()->safeEmail(),
        ],
    ];

    $emailNotificationDTO[] = new FormEmailNotificationData(...$emailNotifications[0]);
    $emailNotificationDTO[] = new FormEmailNotificationData(...$emailNotifications[1]);

    $this->assertDatabaseEmpty(Form::class);
    $this->assertDatabaseEmpty(FormEmailNotification::class);

    app(CreateFormAction::class)
        ->execute(new FormData(
            blueprint_id: $blueprint->getKey(),
            name: $name,
            store_submission: $storeSubmission,
            slug: $slug,
            form_email_notifications: $emailNotificationDTO
        ));
    unset($emailNotificationDTO);

    assertDatabaseCount(Form::class, 1);

    $expected = [
        'blueprint_id' => $blueprint->getKey(),
        'name' => $name,
        'slug' => $slug,
        'store_submission' => $storeSubmission,
    ];
    if ($slug === null) {
        unset($expected['slug']);
    }
    assertDatabaseHas(Form::class, $expected);

    assertDatabaseCount(FormEmailNotification::class, 2);
    assertDatabaseHas(FormEmailNotification::class, $emailNotifications[0]);
    assertDatabaseHas(FormEmailNotification::class, $emailNotifications[1]);
});
