<?php

declare(strict_types=1);

namespace App\Filament\Pages\Concerns;

use App\Filament\Pages\Settings\BaseSettings;
use Carbon\CarbonInterval;
use DateInterval;
use Filament\Forms\ComponentContainer;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Str;
use Spatie\Activitylog\ActivityLogger;
use Spatie\Activitylog\Contracts\Activity;

/**
 * @property-read ComponentContainer $form
 * @property \Illuminate\Database\Eloquent\Model $record
 */
trait LogsFormActivity
{
    protected static string $logName = 'admin';

    protected static bool $logOnlyDirty = true;

    public array $initialFormState;

    public function afterFill(): void
    {
        if ( ! $this->shouldLogInitialState()) {
            return;
        }

        $state = $this->form->getRawState();

        $this->form->dehydrateState($state);
        $this->form->mutateDehydratedState($state);

        $this->initialFormState = ($statePath = $this->form->getStatePath())
            ? data_get($state, $statePath) ?? []
            : $state;
    }

    protected function afterCreate(): void
    {
        $this->logFormActivity('created');
    }

    protected function afterSave(): void
    {
        $this->logFormActivity('updated');
    }

    protected function logFormActivity(string $event, string $description = null): ?Activity
    {
        return app(ActivityLogger::class)
            ->useLog(self::$logName)
            ->event($event)
            ->when(
                $this->hasProperty('record'),
                fn (ActivityLogger $activityLogger) => $activityLogger->performedOn($this->record)
            )
            ->withProperties($this->getActivityProperties())
            ->log($description ?? $this->getDescriptionForEvent($event));
    }

    protected function getActivityProperties(): array
    {
        $properties = ['attributes' => $this->form->getState()];

        if ($this->shouldLogInitialState()) {
            $properties['old'] = $this->initialFormState;
        }

        if (self::$logOnlyDirty && isset($properties['old'])) {
            // Snippet copied from https://github.com/spatie/laravel-activitylog/blob/1f5d1b966187b2d11995d0d1898a2d4fb44b5c67/src/Traits/LogsActivity.php#L295-L319
            $properties['attributes'] = array_udiff_assoc(
                $properties['attributes'],
                $properties['old'],
                function ($new, $old) {
                    // Strict check for php's weird behaviors
                    if ($old === null || $new === null) {
                        return $new === $old ? 0 : 1;
                    }

                    // Handles Date interval comparisons since php cannot use spaceship
                    // Operator to compare them and will throw ErrorException.
                    if ($old instanceof DateInterval) {
                        return CarbonInterval::make($old)?->equalTo($new) ? 0 : 1;
                    } elseif ($new instanceof DateInterval) {
                        return CarbonInterval::make($new)?->equalTo($old) ? 0 : 1;
                    }

                    return $new <=> $old;
                }
            );

            $properties['old'] = collect($properties['old'])
                ->only(array_keys($properties['attributes']))
                ->all();
        }

        return $properties;
    }

    protected function shouldLogInitialState(): bool
    {
        /** @phpstan-ignore-next-line See https://github.com/phpstan/phpstan/issues/3632 */
        return $this instanceof EditRecord || $this instanceof BaseSettings;
    }

    protected function getDescriptionForEvent(string $event): string
    {
        if (method_exists($this, 'getRecordTitle')) {
            return $this->getRecordTitle() . ' ' . $event;
        }

        if (method_exists($this, 'getResource')) {
            return Str::headline(static::getResource()::getModelLabel()) . ' ' . $event;
        }

        return $this->getTitle() . ' ' . $event;
    }
}
