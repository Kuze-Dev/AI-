<?php

declare(strict_types=1);

namespace App\FilamentTenant\Support;

use Domain\Support\RouteUrl\Contracts\HasRouteUrl;
use Filament\Tables;

class RouteUrlTextColumn extends Tables\Columns\TextColumn
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->formatStateUsing(
            fn (HasRouteUrl $record) => $record->activeRouteUrl->url
        );
    }
}
