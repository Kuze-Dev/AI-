<?php

declare(strict_types=1);

namespace HalcyonAgile\FilamentExport;

use HalcyonAgile\FilamentExport\Events\ExportFinishedEvent;
use HalcyonAgile\FilamentExport\Listeners\SendExportFinishedNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider;

class ExportEventServiceProvider extends EventServiceProvider
{
    /** @var array<class-string, array<int, class-string>> */
    protected $listen = [
        ExportFinishedEvent::class => [
            SendExportFinishedNotification::class,
        ],
    ];
}
