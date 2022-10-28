<?php

declare(strict_types=1);

namespace App\Listeners;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Spatie\LaravelSettings\Events\SavingSettings;

class SettingsActivityLog
{
    public function handle(SavingSettings $event): void
    {
        if ($event->originalValues === null) {
            return;
        }

        $attributeChanges = $event->originalValues
            ->diff($event->properties)
            ->keys()
            ->toArray();

        if (blank($attributeChanges)) {
            return;
        }

        activity()
            ->inLog($event->settings::group().'_settings')
            ->causedBy(Auth::user())
            ->withProperties(
                [
                    'old' => Arr::only($event->originalValues->toArray(), $attributeChanges),
                    'attributes' => Arr::only($event->properties->toArray(), $attributeChanges),
                ]
            )
            ->log(Str::headline($event->settings::group()).' Settings Updated.');
    }
}
