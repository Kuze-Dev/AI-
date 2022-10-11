<?php

declare(strict_types=1);

use function Pest\Laravel\get;

it('returns a successfully response', function () {
    get('/')->assertStatus(200);
});
