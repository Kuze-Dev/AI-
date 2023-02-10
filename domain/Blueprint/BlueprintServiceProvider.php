<?php

declare(strict_types=1);

namespace Domain\Blueprint;

use Illuminate\Support\ServiceProvider;

class BlueprintServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->mergeConfigFrom(__DIR__ . '/config/blueprint.php', 'domain.blueprint');
    }
}
