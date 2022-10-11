<?php

declare(strict_types=1);

use Domain\Auth\Support\RecoveryCodeGenerator;

it('can generate recovery codes', function () {
    $recoveryCode = RecoveryCodeGenerator::generate();

    expect($recoveryCode)->toBeString();
});
