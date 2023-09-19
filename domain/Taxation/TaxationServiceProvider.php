<?php

declare(strict_types=1);

namespace Domain\Taxation;

use Illuminate\Support\ServiceProvider;

class TaxationServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->singleton('taxation', Taxation::class);
    }
}
