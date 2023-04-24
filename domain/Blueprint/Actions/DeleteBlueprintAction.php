<?php

declare(strict_types=1);

namespace Domain\Blueprint\Actions;

use Domain\Blueprint\Models\Blueprint;
use Filament\Tables\Actions\Action;

class DeleteBlueprintAction
{
    public function execute(Blueprint $blueprint, Action $action): ?bool
    {
        foreach (array_keys(config('domain.blueprint.relations')) as $relationName) {
            if ($blueprint->{$relationName}()->exists()) {
                $modelName = class_basename($blueprint->{$relationName}()->getRelated());
                $action->failureNotificationTitle(trans("{$modelName} is using this blueprint."))
                    ->failure();

                return false;
            }
        }

        return $blueprint->delete();
    }
}
