<?php

declare(strict_types=1);

namespace HalcyonAgile\FilamentImport;

use HalcyonAgile\FilamentImport\Events\ImportFinished;
use HalcyonAgile\FilamentImport\Listeners\SendImportFinishedNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider;

class ImportEventServiceProvider extends EventServiceProvider
{
    /** @var array<class-string, array<int, class-string>> */
    protected $listen = [
        ImportFinished::class => [
            SendImportFinishedNotification::class,
        ],
    ];
}
