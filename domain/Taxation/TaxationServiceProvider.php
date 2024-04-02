<?php

declare(strict_types=1);

namespace Domain\Taxation;

use Illuminate\Support\ServiceProvider;

class TaxationServiceProvider extends ServiceProvider
{
    #[\Override]
    public function register()
    {
        $this->app->singleton('taxation', Taxation::class);
    }
}
