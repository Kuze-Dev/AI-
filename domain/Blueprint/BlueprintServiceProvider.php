<?php

declare(strict_types=1);

namespace Domain\Blueprint;

use Domain\Blueprint\Models\Blueprint;
use Illuminate\Support\ServiceProvider;

class BlueprintServiceProvider extends ServiceProvider
{
    #[\Override]
    public function register()
    {
        $this->mergeConfigFrom(__DIR__.'/config/blueprint.php', 'domain.blueprint');
    }

    public function boot(): void
    {
        $this->registerBlueprintModelRelationships();
    }

    public function registerBlueprintModelRelationships(): void
    {
        foreach (config('domain.blueprint.relations') as $relationName => $modelClass) {
            Blueprint::resolveRelationUsing(
                $relationName,
                /** @phpstan-ignore argument.templateType */
                fn (Blueprint $model) => $model->hasMany($modelClass)
            );
        }
    }
}
