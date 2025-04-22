<?php

declare(strict_types=1);

namespace Domain\Tier;

use Illuminate\Support\ServiceProvider;

class TierServiceProvider extends ServiceProvider
{
    #[\Override]
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/config/tier.php', 'domain.tier');
    }
}
