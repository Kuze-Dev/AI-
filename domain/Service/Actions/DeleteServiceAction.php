<?php

declare(strict_types=1);

namespace Domain\Service\Actions;

use Domain\Service\Models\Service;

class DeleteServiceAction
{
    public function execute(Service $service, bool $force = false): ?bool
    {
        return $service->{$force ? 'forceDelete' : 'delete'}();
    }
}
