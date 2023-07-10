<?php

declare(strict_types=1);

namespace App\HttpTenantApi\Controllers\Cart;

use Domain\Cart\Actions\CartNotesUpdateAction;
use Domain\Cart\DataTransferObjects\CartNotesUpdateData;
use Domain\Cart\Models\CartLine;
use Domain\Cart\Requests\CartNotesUpdateRequest;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Spatie\RouteAttributes\Attributes\Middleware;
use Spatie\RouteAttributes\Attributes\Resource;

#[
    Resource('carts/remarks', apiResource: true, only: 'store'),
    Middleware(['auth:sanctum'])
]
class CartRemarksController
{
    public function store(CartNotesUpdateRequest $request): mixed
    {
        try {
            $validatedData = $request->validated();

            $payload = CartNotesUpdateData::fromArray($validatedData);

            $result = app(CartNotesUpdateAction::class)
                ->execute($payload);

            if ($result instanceof CartLine) {
                return response()
                    ->json([
                        'message' => 'Notes updated successfully',
                    ]);
            }
        } catch (ModelNotFoundException $e) {
            return response()
                ->json([
                    'error' => 'Cart line not found',
                ], 404);
        }
    }
}
