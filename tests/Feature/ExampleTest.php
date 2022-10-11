<?php

declare(strict_types=1);

use function Pest\Laravel\get;

it('returns a successfull response', function () {
    get('/')->assertStatus(200);
});
