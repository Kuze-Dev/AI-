<?php

declare(strict_types=1);

namespace Domain\Support\RouteUrl;

use Domain\Support\RouteUrl\Models\RouteUrl;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;

final class Support
{
    private function __construct()
    {
    }

    public static function isActiveRouteUrl(string $url, ?Contracts\HasRouteUrl $model): bool
    {
        return RouteUrl::whereIn(
            'id',
            function (Builder $query) {
                $query->select(DB::raw('MAX(id)'))
                    ->from((new RouteUrl())->getTable())
                    ->groupBy('model_type', 'model_id');
            }
        )
            ->whereUrl($url)
            ->when($model !== null, function (\Illuminate\Database\Eloquent\Builder $query) use ($model) {
                /** @var \Illuminate\Database\Eloquent\Model $model */
                $query->where('model_type', '!=', $model->getMorphClass())
                    ->where('model_id', '!=', $model->getKey());
            })
            ->exists();
    }
}
