<?php

declare(strict_types=1);

use App\Filament\Resources\ActivityResource\Pages\ListActivities;

use function Pest\Livewire\livewire;

beforeEach(fn () => loginAsSuperAdmin());

it('can list', function () {
    $activity = activity('admin')
        ->event('some-random-event')
        ->log('Some Random Event');

    livewire(ListActivities::class)
        ->assertCanSeeTableRecords([$activity]);
});

it('can view activity', function () {
    $activity = activity('admin')
        ->event('some-random-event')
        ->log('Some Random Event');

    livewire(ListActivities::class)
        ->callTableAction('view', $activity);
});
