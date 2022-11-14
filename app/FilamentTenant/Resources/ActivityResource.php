<?php

declare(strict_types=1);

namespace App\FilamentTenant\Resources;

use App\Filament\Resources\ActivityResource as BaseActivityResource;
use Artificertech\FilamentMultiContext\Concerns\ContextualResource;

class ActivityResource extends BaseActivityResource
{
    use ContextualResource;
}
