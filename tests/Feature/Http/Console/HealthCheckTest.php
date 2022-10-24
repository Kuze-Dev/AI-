<?php

declare(strict_types=1);

use Spatie\Health\Commands\ListHealthChecksCommand;
use Spatie\Health\Commands\RunHealthChecksCommand;

use function Pest\Laravel\artisan;

it('run artisan command', function () {
    artisan(RunHealthChecksCommand::class)
        ->assertSuccessful();
    artisan(ListHealthChecksCommand::class)
        ->assertSuccessful();
});
