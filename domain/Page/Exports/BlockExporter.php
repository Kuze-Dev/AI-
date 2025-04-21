<?php

declare(strict_types=1);

namespace Domain\Page\Exports;

use App\Jobs\QueueJobPriority;
use Domain\Page\Models\Block;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

class BlockExporter extends Exporter
{
    protected static ?string $model = Block::class;

    public function getJobQueue(): ?string
    {
        return QueueJobPriority::DEFAULT;
    }

    #[\Override]
    public static function getColumns(): array
    {
        return [
            ExportColumn::make('name')
                ->label('name'),
            ExportColumn::make('component')
                ->label('component'),
            ExportColumn::make('is_fixed_content')
                ->label('is_fixed_content'),
            ExportColumn::make('blueprint_id')
                ->label('blueprint_id'),
            ExportColumn::make('sites')
                ->label('sites')
                ->state(function (Block $record) {
                    return implode(',', $record->sites->pluck('domain')->toArray());
                }),
            ExportColumn::make('image')
                ->state(
                    fn (Block $record) => $record->getFirstMediaUrl('image')
                )
                ->label('image'),
            ExportColumn::make('data')
                ->label('data')
                ->state(fn (Block $record) => json_encode($record->data)),
            ExportColumn::make('created_at')
                ->label('created_at')
                ->state(
                    fn (Block $record) => $record->created_at
                        ?->format(Table::$defaultDateTimeDisplayFormat)
                ),
        ];
    }

    #[\Override]
    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = 'Your Block export has completed and '.number_format($export->successful_rows).
            ' '.Str::of('row')->plural($export->successful_rows).' exported.';

        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $body .= ' '.number_format($failedRowsCount).
                ' '.Str::of('row')->plural($failedRowsCount).' failed to export.';
        }

        return $body;
    }

    // public static function modifyQuery(Builder $query): Builder
    // {
    //     dd($query);
    //     return Taxonomy::query()->with('dataTranslation');
    // }
}
