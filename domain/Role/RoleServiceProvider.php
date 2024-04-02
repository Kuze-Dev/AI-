<?php

declare(strict_types=1);

namespace Domain\Role;

use Illuminate\Support\ServiceProvider;

class RoleServiceProvider extends ServiceProvider
{
    #[\Override]
    public function register()
    {
        $this->mergeConfigFrom(__DIR__.'/config/role.php', 'domain.role');
    }
}
