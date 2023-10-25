<?php

declare(strict_types=1);

use Domain\Admin\Actions\RestoreAdminAction;
use Domain\Admin\Database\Factories\AdminFactory;

use function Pest\Laravel\assertNotSoftDeleted;

it('can restore admin', function () {
    $admin = AdminFactory::new()->create();
    $admin->delete();

    $result = app(RestoreAdminAction::class)->execute($admin);

    assertNotSoftDeleted($admin);
    expect($result)->toBeTrue();
});

it('does nothing if admin is not softDelete', function () {
    $admin = AdminFactory::new()->create();

    $result = app(RestoreAdminAction::class)->execute($admin);

    expect($result)->toBeNull();
});
