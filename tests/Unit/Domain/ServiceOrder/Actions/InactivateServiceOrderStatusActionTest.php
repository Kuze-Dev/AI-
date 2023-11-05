<?php

declare(strict_types=1);

use Domain\ServiceOrder\Actions\InactivateServiceOrderStatusAction;
use Domain\ServiceOrder\Database\Factories\ServiceOrderFactory;
use Domain\ServiceOrder\Enums\ServiceOrderStatus;
use Illuminate\Support\Facades\Queue;
use Spatie\QueueableAction\Testing\QueueableActionFake;

beforeEach(function () {
    testInTenantContext();

    $this->serviceOrder = ServiceOrderFactory::new()->create();
});

it('can execute', function () {
    app(InactivateServiceOrderStatusAction::class)->execute($this->serviceOrder);

    expect($this->serviceOrder->status)->toBe(ServiceOrderStatus::INACTIVE);
});

it('can dispatch', function () {
    Queue::fake();

    app(InactivateServiceOrderStatusAction::class)
        ->onQueue()
        ->execute($this->serviceOrder);

    QueueableActionFake::assertPushed(InactivateServiceOrderStatusAction::class);
});
