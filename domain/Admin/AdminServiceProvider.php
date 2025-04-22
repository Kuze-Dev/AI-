<?php

declare(strict_types=1);

namespace Domain\Admin;

use Illuminate\Support\ServiceProvider;

class AdminServiceProvider extends ServiceProvider
{
    #[\Override]
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/config/admin.php', 'domain.admin');
    }
}
