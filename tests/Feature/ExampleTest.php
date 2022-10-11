<?php

use function Pest\Laravel\get;

it('returns a successfull response', function () {
    get('/')->assertStatus(200);
});
