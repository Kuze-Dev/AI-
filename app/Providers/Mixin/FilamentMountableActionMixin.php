<?php

declare(strict_types=1);

namespace App\Providers\Mixin;

use Closure;
use Filament\Actions\Contracts\HasRecord;
use Filament\Actions\MountableAction;
use Filament\Facades\Filament;
use Filament\Tables;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Spatie\Activitylog\ActivityLogger;
use Spatie\Activitylog\ActivitylogServiceProvider;

class FilamentMountableActionMixin
{
    public function withActivityLog(): Closure
    {
        return fn (string $logName = 'admin', Closure|string|null $event = null, Closure|string|null $description = null, Closure|array|null $properties = null, Model|int|string|null $causedBy = null): MountableAction =>
            /** @var MountableAction $this */
            $this->after(function (MountableAction $action) use ($logName, $event, $description, $properties, $causedBy) {
                $event = $action->evaluate($event) ?? $action->getName();
                $properties = $action->evaluate($properties);
                $description = Str::headline($action->evaluate($description ?? $event) ?? $action->getName());
                $causedBy ??= Filament::auth()->user();

                $log = function (?Model $model) use ($properties, $event, $logName, $description, $causedBy): void {
                    if ($model && $model::class === ActivitylogServiceProvider::determineActivityModel()) {
                        return;
                    }

                    $activityLogger = app(ActivityLogger::class)
                        ->useLog($logName)
                        ->event($event)
                        ->causedBy($causedBy);

                    if ($model) {
                        $activityLogger->performedOn($model);
                    }

                    if ($model && in_array($event, ['deleted', 'restored', 'force-deleted'])) {
                        $attributes = method_exists($model, 'attributesToBeLogged')
                            ? $model->only($model->attributesToBeLogged())
                            : $model->attributesToArray();

                        $activityLogger->withProperties([
                            ($event === 'restored') ? 'attributes' : 'old' => $attributes,
                            ($event !== 'restored') ? 'attributes' : 'old' => [],
                        ]);
                    } elseif ($properties) {
                        $activityLogger->withProperties($properties);
                    }

                    $activityLogger->log($description);
                };

                if ($action instanceof Tables\Actions\BulkAction) {

                    if ($action instanceof Tables\Actions\ExportBulkAction) {
                        $MODEL = $action->getExporter()::getModel();
                        $action->getRecords()
                            ?->each(fn (int|string $modelKey) => $log($MODEL::find($modelKey)));
                    } else {
                        $action->getRecords()
                            ?->each(fn (Model $model) => $log($model));
                    }

                    return;
                }

                $log($action instanceof HasRecord ? $action->getRecord() : null);
            });
    }
}
