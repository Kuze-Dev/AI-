<?php

declare(strict_types=1);

namespace Support\RouteUrl\Contracts;

use Domain\Content\Models\ContentEntry;
use Domain\Page\Models\Page;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphOne;

/**
 * @property-read \Illuminate\Database\Eloquent\Collection|\Support\RouteUrl\Models\RouteUrl[] $routeUrls
 * @property-read \Support\RouteUrl\Models\RouteUrl|null $activeRouteUrl
 * @property-read ContentEntry|Page|null $pageDraft
 * @property-read ContentEntry|Page|null $parentPage.
 *
 * @mixin \Illuminate\Database\Eloquent\Model
 *
 * @template TRelatedModel of \Support\RouteUrl\Models\RouteUrl
 * @template TDeclaringModel of \Illuminate\Database\Eloquent\Model
 *
 * @extends \Illuminate\Database\Eloquent\Relations\MorphOneOrMany<TRelatedModel, TDeclaringModel, ?TRelatedModel>
 * @phpstan-ignore-next-line */
interface HasRouteUrl
{
    /**
     * @return \Illuminate\Database\Eloquent\Relations\MorphOne<TRelatedModel, TDeclaringModel>
     */
    public function routeUrls(): MorphOne;

    /**
     * @return \Illuminate\Database\Eloquent\Relations\MorphOne<TRelatedModel, TDeclaringModel>
     */
    public function activeRouteUrl(): MorphOne;

    public static function generateRouteUrl(Model $model, array $attributes): string;
}
