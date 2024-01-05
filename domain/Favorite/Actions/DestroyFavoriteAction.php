<?php

declare(strict_types=1);

namespace Domain\Favorite\Actions;

use Domain\Favorite\DataTransferObjects\FavoriteData;
use Domain\Favorite\Models\Favorite;
use Exception;

class DestroyFavoriteAction
{
    public function execute(FavoriteData $favoriteData): bool
    {
        try {
            $favorite = Favorite::where('product_id', $favoriteData->product_id)->where('customer_id', $favoriteData->customer_id)
                ->firstOrFail();

            $favorite->delete();

            return true;
        } catch (Exception) {
            return false;
        }
    }
}
