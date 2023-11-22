<?php

declare(strict_types=1);

namespace Support\Excel;

use Illuminate\Foundation\Support\Providers\EventServiceProvider;
use Illuminate\Support\Facades\Route;
use Support\Excel\Commands\PruneExcelCommand;
use Support\Excel\Http\Controllers\DownloadExportController;

class ExcelServiceProvider extends EventServiceProvider
{
    /** @var array<class-string, array<int, class-string>> */
    protected $listen = [
        Events\ExportFinished::class => [
            Listeners\SendExportFinishedNotification::class,
        ],
        Events\ImportFinished::class => [
            Listeners\SendImportFinishedNotification::class,
        ],
    ];

    public function register(): void
    {
        parent::register();

        $this->mergeConfigFrom(__DIR__.'/config/excel.php', 'support.excel');
    }

    public function boot(): void
    {
        Route::get(config('support.excel.path').'/{path}', DownloadExportController::class)
            ->middleware(array_merge(array_merge(
                config('filament.middleware.auth'),
                config('filament.middleware.base'),
                config('support.excel.middleware'),
            ), [
                'tenant',
            ]))
            ->where('path', '.*')
            ->name('filament-excel.download-export');

        if ($this->app->runningInConsole()) {
            $this->commands([PruneExcelCommand::class]);
        }
    }
}
