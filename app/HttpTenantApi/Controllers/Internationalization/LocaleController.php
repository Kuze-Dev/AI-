<?php

declare(strict_types=1);

namespace App\HttpTenantApi\Controllers\Internationalization;

use App\Features\CMS\CMSBase;
use Domain\Internationalization\Models\Locale;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Spatie\RouteAttributes\Attributes\ApiResource;
use Spatie\RouteAttributes\Attributes\Middleware;

#[
    ApiResource('locales', only: ['index']),
    Middleware('feature.tenant:'.CMSBase::class)
]
class LocaleController
{
    /** @return Collection<int, Locale> */
    public function index()
    {
        return Cache::rememberForever('locale', fn () => Locale::all());
    }
}
