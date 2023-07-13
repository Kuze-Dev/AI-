<?php

namespace Domain\Taxation;

use Illuminate\Support\ServiceProvider;

class TaxationServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->singleton('taxation', Taxation::class);
    }
}
