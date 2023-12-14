<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Support\Excel\Commands\PruneExcelCommand;

use function Pest\Laravel\artisan;
use function Pest\Laravel\travelTo;

beforeEach(function () {
    config([
        'support.excel.export_expires_in_minute' => 5,
    ]);

    // freeze time
    travelTo(now());
});

dataset('expires_dataset', ['expired' => true, 'not expired' => false]);

it('prune export', function (bool $expired) {
    $exportsDirectory = Str::finish(config('support.excel.temporary_files.base_directory'), '/exports/');

    Storage::disk(config('support.excel.temporary_files.disk'))
        ->put($exportsDirectory.'test-export.csv', '');

    $minutes = config('support.excel.export_expires_in_minute');

    if ($expired) {
        $minutes++;
    }

    travelTo(now()->addMinutes($minutes));

    artisan(PruneExcelCommand::class)
        ->assertSuccessful();

    if ($expired) {
        Storage::disk(config('support.excel.temporary_files.disk'))->assertDirectoryEmpty($exportsDirectory);
    } else {
        Storage::disk(config('support.excel.temporary_files.disk'))->assertExists($exportsDirectory.'test-export.csv');
    }
})
    ->with('expires_dataset');
