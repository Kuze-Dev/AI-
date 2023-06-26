<?php

declare(strict_types=1);

namespace Support\Excel\Http\Controllers;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DownloadExportController
{
    public function __invoke(string $path): StreamedResponse
    {
        $path = Str::finish(config('support.excel.temporary_files.base_directory'), '/exports/') . $path;

        if ( ! Storage::disk(config('support.excel.temporary_files.disk'))->exists($path)) {
            abort(404);
        }

        dispatch(fn () => Storage::disk(config('support.excel.temporary_files.disk'))->delete($path))->afterResponse();

        return Storage::disk(config('support.excel.temporary_files.disk'))->download($path);
    }
}
