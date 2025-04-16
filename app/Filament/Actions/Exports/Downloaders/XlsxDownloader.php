<?php

declare(strict_types=1);

namespace App\Filament\Actions\Exports\Downloaders;

use Filament\Actions\Exports\Downloaders\Contracts\Downloader;
use Filament\Actions\Exports\Models\Export;
use Illuminate\Support\Facades\Storage;
use League\Csv\Reader as CsvReader;
use League\Csv\Statement;
use OpenSpout\Common\Entity\Row;
use Illuminate\Support\Str;
use OpenSpout\Writer\XLSX\Writer;
use Symfony\Component\HttpFoundation\StreamedResponse;

class XlsxDownloader implements Downloader
{
    public function __invoke(Export $export): StreamedResponse
    {
        $disk = $export->getFileDisk();
        $directory = $export->getFileDirectory();

        if (! $disk->exists($directory)) {
            abort(404);
        }

        $fileName = $export->file_name.'.xlsx';

        if ($disk->exists($filePath = $directory.DIRECTORY_SEPARATOR.$fileName)) {
            // $response = $disk->download($filePath);

            // if (ob_get_length() > 0) {
            //     ob_end_clean();
            // }

            // return $response;
            // Use the Storage facade to download the file
            // and set the X-Vapor-Base64-Encode header
            return Storage::download(path: $filePath, name: null, headers: [
                'X-Vapor-Base64-Encode' => 'True',
            ]);
        }

        $writer = app(Writer::class);

        $csvDelimiter = $export->exporter::getCsvDelimiter();

        $writeRowsFromFile = function (string $file) use ($csvDelimiter, $disk, $writer) {
            /** @phpstan-ignore-next-line  derivative of Vendor XlsxDownloader */
            $csvReader = CsvReader::createFromStream($disk->readStream($file));
            $csvReader->setDelimiter($csvDelimiter);
            /** @phpstan-ignore-next-line  derivative of Vendor XlsxDownloader */
            $csvResults = Statement::create()->process($csvReader);

            foreach ($csvResults->getRecords() as $row) {
                /** @phpstan-ignore-next-line  derivative of Vendor XlsxDownloader */
                $writer->addRow(Row::fromValues($row));
            }
        };

        return response()->streamDownload(function () use ($disk, $directory, $fileName, $writer, $writeRowsFromFile) {
            $writer->openToBrowser($fileName);

            $writeRowsFromFile($directory.DIRECTORY_SEPARATOR.'headers.csv');

            foreach ($disk->files($directory) as $file) {
                /** phpstan doesn't allow use of global helpers */
                if (Str::of($file)->endsWith('headers.csv')) {
                    continue;
                }
                /** phpstan doesn't allow use of global helpers */
                if (! Str::of($file)->endsWith('.csv')) {
                    continue;
                }

                $writeRowsFromFile($file);
            }

            $writer->close();
        }, $fileName, [
            'Content-Type' => 'application/vnd.ms-excel',
        ]);
    }
}
