<?php

declare(strict_types=1);

use App\Jobs\QueueJobPriority;
use Illuminate\Support\Facades\Artisan;

Artisan::command('app:horizon:clear', function () {
    /** @var Illuminate\Foundation\Console\ClosureCommand $this */
    foreach (QueueJobPriority::PRIORITIES as $queueName) {
        Artisan::call('horizon:clear', ['--queue' => $queueName, '--force' => true]);
        $this->info(Artisan::output());
    }

    $this->info('Done clear jobs on horizon..');
});
