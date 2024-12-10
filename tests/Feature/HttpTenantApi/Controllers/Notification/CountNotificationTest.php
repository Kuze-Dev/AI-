<?php

declare(strict_types=1);

use Domain\Customer\Database\Factories\CustomerFactory;
use Tests\Fixtures\TestNotification;

use function Pest\Laravel\withHeader;

beforeEach(function () {
    testInTenantContext();
    $customer = CustomerFactory::new()->createOne();

    withHeader('Authorization', 'Bearer '.$customer
        ->createToken('testing-auth')
        ->plainTextToken);

    $this->customer = $customer;

    return $customer;
});

it('can count notification', function () {

    $user = $this->customer;
    $user->notify(new TestNotification(fake()->sentence()));
    $user->notify(new TestNotification(fake()->sentence()));

    $unreadCount = $user->notifications()->whereNull('read_at')->count();

    $response = $this->getJson('api/notifications/count');

    $response->assertStatus(200);
    $response->assertJson(['count' => $unreadCount]);

});
