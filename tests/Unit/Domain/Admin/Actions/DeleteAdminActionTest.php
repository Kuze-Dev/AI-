<?php

declare(strict_types=1);

use Database\Factories\AdminFactory;
use Domain\Admin\Actions\DeleteAdminAction;
use Domain\Admin\Exceptions\CantDeleteSuperAdminException;

use function Pest\Laravel\assertModelMissing;
use function Pest\Laravel\assertSoftDeleted;

it('can soft delete admin', function () {
    $admin = AdminFactory::new()->create(['id' => 2]);

    $result = app(DeleteAdminAction::class)->execute($admin);

    assertSoftDeleted($admin);
    expect($result)->toBeTrue();
});

it('can force delete admin', function () {
    $admin = AdminFactory::new()->create(['id' => 2]);

    $result = app(DeleteAdminAction::class)->execute($admin, true);

    assertModelMissing($admin);
    expect($result)->toBeTrue();
});

it('can\'t delete super admin', function (bool $force) {
    $admin = AdminFactory::new()->create();

    app(DeleteAdminAction::class)->execute($admin, $force);
})->with([
    'soft delete' => false,
    'force delete' => true,
])->throws(CantDeleteSuperAdminException::class);
