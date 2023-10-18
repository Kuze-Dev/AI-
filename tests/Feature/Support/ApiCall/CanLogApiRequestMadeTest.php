<?php

declare(strict_types=1);

use Support\ApiCall\Models\ApiCall;

use function Pest\Laravel\assertDatabaseCount;
use function Pest\Laravel\getJson;

beforeEach(fn () => testInTenantContext());

// it('can log api routes', function () {

//     getJson('api/pages')
//         ->assertOk();

//     assertDatabaseCount(ApiCall::class, 1);

// });
