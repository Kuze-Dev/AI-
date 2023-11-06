<?php

declare(strict_types=1);

use Domain\Customer\Database\Factories\CustomerFactory;
use Domain\Notification\Events\NotificationRead;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Support\Facades\Event;
use Tests\Fixtures\TestNotification;

use function Pest\Laravel\patchJson;
use function Pest\Laravel\withHeader;
use function PHPUnit\Framework\assertTrue;

// beforeEach(function () {
//     testInTenantContext();
//     $customer = CustomerFactory::new()->createOne();

//     withHeader('Authorization', 'Bearer ' . $customer
//         ->createToken('testing-auth')
//     ->plainTextToken);

//     $this->customer = $customer;

//     return $customer;
// });

// it('mark as read', function () {
//     Event::fake();

//     $user = $this->customer;
//     $user->notify(new TestNotification(fake()->sentence()));

//     /** @var DatabaseNotification $notification */
//     $notification = DatabaseNotification::first();

//     assertTrue($notification->unread());

//     patchJson('api/notifications/' . $notification->getRouteKey() . '/mark-as-read')
//         ->assertOk()
//         ->assertValid();

//     Event::assertDispatched(NotificationRead::class, 1);

//     assertTrue($notification->refresh()->read());
// });
