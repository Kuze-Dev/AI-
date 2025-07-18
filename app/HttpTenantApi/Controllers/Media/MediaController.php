<?php

declare(strict_types=1);

namespace App\HttpTenantApi\Controllers\Media;

use App\Features\CMS\CMSBase;
use App\Http\Middleware\TenantApiAuthorizationMiddleware;
use App\HttpTenantApi\Controllers\BaseCms\BaseCmsController;
use App\HttpTenantApi\Resources\MediaResource;
use Domain\Tenant\Support\ApiAbilitties;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Spatie\QueryBuilder\QueryBuilder;
use Spatie\RouteAttributes\Attributes\Get;
use Spatie\RouteAttributes\Attributes\Middleware;
use TiMacDonald\JsonApi\JsonApiResourceCollection;

#[
    Middleware(['feature.tenant:'.CMSBase::class, TenantApiAuthorizationMiddleware::class])
]
class MediaController extends BaseCmsController
{
    #[Get('media/request-media', name: 'media.list')]
    public function requestMedia(): JsonApiResourceCollection
    {
        $this->checkAbilities(ApiAbilitties::media_view->value);

        $media_uuid = request()->get('media_uuid');

        return MediaResource::collection(
            QueryBuilder::for(
                Media::query()->whereIN('uuid', explode(',', $media_uuid))
            )
                ->allowedFilters([
                'uuid',
            ])
                ->jsonPaginate()
        );
    }
}
