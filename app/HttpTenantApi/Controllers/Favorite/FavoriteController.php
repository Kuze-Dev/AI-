<?php

declare(strict_types=1);

namespace App\HttpTenantApi\Controllers\Favorite;

use App\HttpTenantApi\Resources\FavoriteResource;
use Domain\Favorite\Models\Favorite;
use Domain\Favorite\Requests\FavoriteStoreRequest;
use Spatie\QueryBuilder\QueryBuilder;
use Spatie\RouteAttributes\Attributes\Resource;
use Symfony\Component\HttpFoundation\JsonResponse;
use TiMacDonald\JsonApi\JsonApiResourceCollection;
use Spatie\RouteAttributes\Attributes\Middleware;

#[
    Resource('favorites', apiResource: true, except: ['index', 'update']),
    Middleware(['auth:sanctum'])
]
class FavoriteController
{
    public function show(): JsonApiResourceCollection
    {
        $customer = auth()->user();
        return FavoriteResource::collection(
            QueryBuilder::for(Favorite::whereCustomerId($customer->id))
                ->allowedIncludes([
                    'product',
                    'customer',
                ])
                ->get()
        );
    }

    public function store(FavoriteStoreRequest $request, Favorite $favorite): JsonResponse
    {

        $customer = auth()->user();
        $validatedData = $request->validated();
        $favorite->product_id = $validatedData['product_id'];
        $favorite->customer_id = $customer->id;

        $favorite->save();

        return response()->json(['message' => 'Favorite item created successfully'], 201);
    }

    public function destroy(int $favorite): JsonResponse
    {
        $favorite = Favorite::where('id', $favorite)
            ->firstOrFail();

        $favorite->delete();

        return response()->json(['message' => 'Favorite item deleted']);
    }
}
