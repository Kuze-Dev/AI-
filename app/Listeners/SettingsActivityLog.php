<?php

declare(strict_types=1);

namespace App\Listeners;

use BackedEnum;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Spatie\LaravelSettings\Events\SavingSettings;
use UnitEnum;

class SettingsActivityLog
{
    public function handle(SavingSettings $event): void
    {
        if ($event->originalValues === null) {
            return;
        }

        $attributeChanges = $event->originalValues
            ->map(fn (mixed $value) => match (true) {
                $value instanceof BackedEnum => $value->value,
                $value instanceof UnitEnum => $value->name,
                default => $value,
            })
            ->diff(
                $event->properties
                    ->map(fn (mixed $value) => match (true) {
                        $value instanceof BackedEnum => $value->value,
                        $value instanceof UnitEnum => $value->name,
                        default => $value,
                    })
            )
            ->keys()
            ->toArray();

        if (blank($attributeChanges)) {
            return;
        }

        activity()
            ->inLog($event->settings::group() . '_settings')
            ->causedBy(Auth::user())
            ->withProperties(
                [
                    'old' => Arr::only($event->originalValues->toArray(), $attributeChanges),
                    'attributes' => Arr::only($event->properties->toArray(), $attributeChanges),
                ]
            )
            ->log(Str::headline($event->settings::group()) . ' Settings Updated.');
    }
}
