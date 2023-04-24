<?php

declare(strict_types=1);

use Domain\Blueprint\Actions\DeleteBlueprintAction;
use Domain\Blueprint\Database\Factories\BlueprintFactory;

use function Pest\Laravel\assertModelMissing;

beforeEach(fn () => testInTenantContext());

it('can delete blueprint', function (Filament\Tables\Actions\Action $action) {
    $blueprint = BlueprintFactory::new()->withDummySchema()->createOne();

    $result = app(DeleteBlueprintAction::class)->execute($blueprint, $action);

    assertModelMissing($blueprint);
    expect($result)->toBeTrue();
})->with([]);
