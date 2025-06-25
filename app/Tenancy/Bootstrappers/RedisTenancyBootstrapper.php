<?php

declare(strict_types=1);

namespace App\Tenancy\Bootstrappers;

use Illuminate\Contracts\Config\Repository;
use Illuminate\Support\Facades\Redis;
use Stancl\Tenancy\Contracts\TenancyBootstrapper;
use Stancl\Tenancy\Contracts\Tenant;

class RedisTenancyBootstrapper implements TenancyBootstrapper
{
    /** @var array<string, string> Original prefixes of connections */
    public array $originalPrefixes = [];

    protected Repository $config;

    public function __construct(Repository $config)
    {
        $this->config = $config;
    }

    public function bootstrap(Tenant $tenant): void
    {
        if (! app()->environment('testing') && config('session.driver') === 'redis') {
            foreach ($this->prefixedConnections() as $connection) {
                $prefix = $this->config->get('tenancy.redis.prefix_base').$tenant->getTenantKey();
                $client = Redis::connection($connection)->client();

                $this->originalPrefixes[$connection] = $client->getOption(\Redis::OPT_PREFIX);
                $client->setOption(\Redis::OPT_PREFIX, $prefix);
            }
        }

    }

    public function revert(): void
    {
        if (! app()->environment('testing') && config('session.driver') === 'redis') {
            foreach ($this->prefixedConnections() as $connection) {
                $client = Redis::connection($connection)->client();

                $client->setOption(\Redis::OPT_PREFIX, $this->originalPrefixes[$connection]);
            }

            $this->originalPrefixes = [];
        }
    }

    protected function prefixedConnections(): array
    {
        return $this->config->get('tenancy.redis.prefixed_connections');
    }
}
