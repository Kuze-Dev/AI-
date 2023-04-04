<?php

declare(strict_types=1);

namespace Domain\Support\RouteUrl;

use Domain\Support\RouteUrl\Models\RouteUrl;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

final class Support
{
    private function __construct()
    {
    }

    /** @return \Illuminate\Database\Eloquent\Builder<RouteUrl>|RouteUrl */
    public static function activeQueryBuilder(): Builder
    {
        return RouteUrl::select([
            'model_type',
            'model_id',
            DB::raw('MAX(created_at) as latest_created_at'),
        ])
            ->groupBy('model_type', 'model_id')
            ->latest('latest_created_at');
    }
}
