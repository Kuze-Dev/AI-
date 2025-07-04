<?php

declare(strict_types=1);

namespace App\Jobs;

use Filament\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class BulkDeleteJob implements ShouldQueue
{
    use Queueable;

    /**
     * The records to be deleted.
     */
    protected \Illuminate\Database\Eloquent\Collection $records;

    /**
     * The total number of records to be deleted.
     */
    protected int $total;

    /**
     * The admin who initiated the deletion.
     */
    protected \Domain\Admin\Models\Admin $admin;

    /**
     * The name of the model being deleted.
     */
    protected string $modelName;

    public function __construct(
        \Illuminate\Database\Eloquent\Collection $records,
        \Domain\Admin\Models\Admin $admin,
        string $model_name
    ) {
        $this->records = $records;

        $this->total = $records->count();

        $this->admin = $admin;

        $this->modelName = $model_name;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $this->records->each(
            fn (\Illuminate\Database\Eloquent\Model $record) => $record->delete()
        );

        Notification::make()
            ->title('Selected records successfully deleted')
            ->icon('heroicon-o-trash')
            ->iconColor('danger')
            ->body(fn () => trans('Total of :count :model_name records deleted', [
                'count' => $this->total,
                'model_name' => $this->modelName,
            ])
            )
            ->success()
            ->sendToDatabase($this->admin);
    }
}
