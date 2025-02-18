<?php

declare(strict_types=1);

namespace App\HttpTenantApi\Controllers\Favorite;

use App\Attributes\CurrentApiCustomer;
use App\HttpTenantApi\Resources\FavoriteResource;
use Domain\Customer\Models\Customer;
use Domain\Favorite\Actions\CreateFavoriteAction;
use Domain\Favorite\Actions\DestroyFavoriteAction;
use Domain\Favorite\DataTransferObjects\FavoriteData;
use Domain\Favorite\Models\Favorite;
use Domain\Favorite\Requests\FavoriteStoreRequest;
use Illuminate\Auth\AuthenticationException;
use Spatie\QueryBuilder\QueryBuilder;
use Spatie\RouteAttributes\Attributes\Middleware;
use Spatie\RouteAttributes\Attributes\Resource;
use Symfony\Component\HttpFoundation\JsonResponse;
use TiMacDonald\JsonApi\JsonApiResourceCollection;

#[
    Resource('favorites', apiResource: true, except: ['update', 'show']),
    Middleware(['auth:sanctum'])
]
class FavoriteController
{
    public function index(#[CurrentApiCustomer] Customer $customer): JsonApiResourceCollection
    {

        return FavoriteResource::collection(
            QueryBuilder::for(Favorite::whereCustomerId($customer->id))
                ->allowedIncludes([
                    'product',
                    'customer',
                    'product.media',
                ])
                ->allowedSorts([
                    'updated_at',
                ])
                ->get()
        );
    }

    public function store(FavoriteStoreRequest $request, CreateFavoriteAction $createFavoriteAction, #[CurrentApiCustomer] Customer $customer): JsonResponse
    {
        $validatedData = $request->validated();

        $favoriteData = FavoriteData::fromArray([
            'customer_id' => $customer->id,
            'product_id' => $validatedData['product_id'],
        ]);

        if ($createFavoriteAction->execute($favoriteData)) {
            return response()->json(['message' => 'Favorite item created successfully'], 201);
        } else {
            return response()->json(['message' => 'Failed to create favorite item'], 500);
        }
    }

    public function destroy(int $favorite, DestroyFavoriteAction $destroyFavoriteAction,#[CurrentApiCustomer] Customer $customer): JsonResponse
    {

        $favoriteData = FavoriteData::fromArray([
            'customer_id' => $customer->id,
            'product_id' => $favorite,
        ]);

        if ($destroyFavoriteAction->execute($favoriteData)) {
            return response()->json(['message' => 'Favorite item deleted successfully'], 201);
        } else {
            return response()->json(['message' => 'Failed to delete favorite item'], 500);
        }
    }
}
