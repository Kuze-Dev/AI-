<?php

declare(strict_types=1);

namespace App\HttpTenantApi\Controllers\Internationalization;

use App\Features\CMS\CMSBase;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Domain\Internationalization\Models\Locale;
use Spatie\RouteAttributes\Attributes\Middleware;
use Spatie\RouteAttributes\Attributes\ApiResource;

#[
    ApiResource('locale', only: ['index']),
    Middleware('feature.tenant:'. CMSBase::class)
]
class LocaleController
{
    /** @return Collection<int, Locale> */
    public function index()
    {
        return Cache::rememberForever('locale', function () {
            return Locale::all();
        });
    }
}
