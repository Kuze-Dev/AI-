<?php

namespace Support\RouteUrl\EloquentBuilder;
use Illuminate\Database\Eloquent\Builder;
use Support\RouteUrl\Models\RouteUrl;

/**
 * @template TModel of RouteUrl
 *
 * @extends \Illuminate\Database\Eloquent\Builder<TModel>
 *
 * @mixin TModel
 */
class RouteUrlEloquentBuilder extends Builder {}
