<?php

declare(strict_types=1);

namespace Domain\Customer\Export;

use Domain\Customer\Models\Customer;
use Filament\Support\Actions\Action;
use HalcyonAgile\FilamentExport\Actions\ExportAction;
use HalcyonAgile\FilamentExport\Actions\ExportBulkAction;
use Illuminate\Database\Eloquent\Builder;
use Laravel\SerializableClosure\Exceptions\PhpVersionNotSupportedException;

final class Exports
{
    private function __construct()
    {
    }

    /**
     * @throws PhpVersionNotSupportedException
     * @throws \Exception
     */
    public static function headerList(): Action
    {
        return ExportAction::make()
            ->model(Customer::class)
            ->queue()
            ->query(
                fn (Builder $query) => $query
                    ->with('tier')
                    ->latest()
            )
            ->mapUsing(
                ['CUID', 'Email', 'First Name',  'Last Name', 'Mobile', 'Gender', 'Status', 'Birth Date', 'Tier', 'Created At'],
                fn (Customer $customer): array => [
                    $customer->cuid,
                    $customer->email,
                    $customer->first_name,
                    $customer->last_name,
                    $customer->mobile,
                    $customer->gender?->value,
                    $customer->status?->value,
                    $customer->birth_date?->format(config('tables.date_format')),
                    $customer->tier?->name,
                    $customer->created_at?->format(config('tables.date_time_format')),
                ]
            )
            ->tags([
                'tenant:'.(tenant('id') ?? 'central'),
            ])
            ->withActivityLog(
                event: 'exported',
                description: fn (ExportAction $action) => 'Exported '.$action->getModelLabel(),
            );
    }

    /**
     * @throws PhpVersionNotSupportedException
     * @throws \Exception
     */
    public static function tableBulk(): Action
    {
        return ExportBulkAction::make()
            ->queue()
            ->query(
                fn (Builder $query) => $query
                    ->with('tier')
                    ->latest()
            )
            ->mapUsing(
                ['CUID', 'Email', 'First Name',  'Last Name', 'Mobile', 'Status', 'Birth Date', 'Tier', 'Created At'],
                fn (Customer $customer): array => [
                    $customer->cuid,
                    $customer->email,
                    $customer->first_name,
                    $customer->last_name,
                    $customer->mobile,
                    $customer->status?->value,
                    $customer->birth_date?->format(config('tables.date_format')),
                    $customer->tier?->name,
                    $customer->created_at?->format(config('tables.date_time_format')),
                ]
            )
            ->tags([
                'tenant:'.(tenant('id') ?? 'central'),
            ])
            ->withActivityLog(
                event: 'bulk-exported',
                description: fn (ExportBulkAction $action) => 'Bulk Exported '.$action->getModelLabel(),
                properties: fn (ExportBulkAction $action) => ['selected_record_ids' => $action->getRecords()?->modelKeys()]
            );
    }
}
