<?php

declare(strict_types=1);

namespace App\Filament\Pages\Concerns;

use App\Filament\Clusters\Settings\Pages\BaseSettings;
use Carbon\CarbonInterval;
use DateInterval;
use Filament\Forms\ComponentContainer;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Spatie\Activitylog\ActivityLogger;
use Spatie\Activitylog\Contracts\Activity;

/**
 * @property-read ComponentContainer $form
 * @property \Illuminate\Database\Eloquent\Model $record
 */
trait LogsFormActivity
{
    public array $initialFormState;

    public function afterFill(): void
    {
        $state = $this->form->getRawState();
        /** @phpstan-ignore argument.type */
        $this->form->dehydrateState($state);
        /** @phpstan-ignore argument.type */
        $this->form->mutateDehydratedState($state);

        $this->initialFormState = ($statePath = $this->form->getStatePath())
            ? data_get($state, $statePath) ?? []
            : $state;
    }

    protected function afterCreate(): void
    {
        if ($this->formIsDirty()) {
            $this->logFormActivity('created');
        }
    }

    protected function afterSave(): void
    {
        if ($this->formIsDirty()) {
            $this->logFormActivity('updated');
        }
    }

    protected function formIsDirty(): bool
    {
        return count($this->formChanges()) > 0;
    }

    protected function logFormActivity(string $event, ?string $description = null): ?Activity
    {
        $activityLogger = app(ActivityLogger::class)
            ->useLog($this->getLogName())
            ->event($event)
            ->withProperties($this->getActivityProperties());

        if ($record = $this->logsPerformedOn()) {
            $activityLogger->performedOn($record);
        }

        return $activityLogger->log($description ?? $this->getDescriptionForEvent($event));
    }

    protected function getLogName(): string
    {
        return 'admin';
    }

    protected function getActivityProperties(): array
    {
        $properties = ['attributes' => $this->form->getState()];

        if ($this->shouldLogInitialState()) {
            $properties['old'] = $this->initialFormState;
        }

        if ($this->shouldLogOnlyDirty() && isset($properties['old'])) {
            $properties['attributes'] = $this->formChanges();
            $properties['old'] = collect($properties['old'])
                ->only(array_keys($properties['attributes']))
                ->all();
        }

        return $properties;
    }

    protected function shouldLogOnlyDirty(): bool
    {
        return true;
    }

    protected function shouldLogInitialState(): bool
    {
        /** @phpstan-ignore instanceof.alwaysFalse, instanceof.alwaysFalse, instanceof.alwaysFalse, instanceof.alwaysFalse, instanceof.alwaysFalse, instanceof.alwaysFalse, instanceof.alwaysFalse, instanceof.alwaysFalse, instanceof.alwaysFalse, instanceof.alwaysFalse, instanceof.alwaysFalse, instanceof.alwaysFalse, instanceof.alwaysFalse, instanceof.alwaysFalse, instanceof.alwaysFalse, instanceof.alwaysFalse, instanceof.alwaysFalse, instanceof.alwaysFalse, instanceof.alwaysFalse, instanceof.alwaysFalse, instanceof.alwaysFalse, instanceof.alwaysFalse (See https://github.com/phpstan/phpstan/issues/3632) */
        return $this instanceof EditRecord || $this instanceof BaseSettings;
    }

    protected function formChanges(): array
    {
        // Snippet copied from https://github.com/spatie/laravel-activitylog/blob/1f5d1b966187b2d11995d0d1898a2d4fb44b5c67/src/Traits/LogsActivity.php#L295-L319
        return array_udiff_assoc(
            $this->form->getState(),
            $this->initialFormState,
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
    }

    protected function logsPerformedOn(): ?Model
    {
        return $this->hasProperty('record') ? $this->record : null;
    }

    protected function getDescriptionForEvent(string $event): string
    {
        /** @phpstan-ignore function.alreadyNarrowedType, notIdentical.alwaysTrue */
        if (method_exists($this, 'getResource')) {
            /** @phpstan-ignore function.alreadyNarrowedType, notIdentical.alwaysTrue */
            if (method_exists($this, 'getRecord') && ($record = $this->getRecord()) !== null) {
                return $record->getAttribute($this->getResource()::getRecordTitleAttribute());
            }

            return Str::headline($this->getResource()::getModelLabel()).' '.$event;
        }

        return Str::of($this::class)->classBasename()->headline()->toString().' '.$event;
    }
}
