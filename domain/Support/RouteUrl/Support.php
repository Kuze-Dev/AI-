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

    public static function activeQueryBuilderExists(string $url, ?Contracts\HasRouteUrl $model = null): bool
    {
        return RouteUrl::select([
            'model_type',
            'model_id',
            DB::raw('MAX(created_at) as latest_created_at'),
        ])
            ->groupBy('model_type', 'model_id')
            ->latest('latest_created_at')
            ->whereUrl($url)
            ->when($model !== null, function (Builder $query) use ($model) {
                /** @var \Illuminate\Database\Eloquent\Model $model */
                $query->where('model_type', '!=', $model->getMorphClass())
                    ->where('model_id', '!=', $model->getKey());
            })
            ->exists();
    }
}
