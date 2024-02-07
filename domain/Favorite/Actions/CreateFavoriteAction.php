<?php

declare(strict_types=1);

namespace Domain\Favorite\Actions;

use Domain\Favorite\DataTransferObjects\FavoriteData;
use Domain\Favorite\Models\Favorite;
use Exception;

class CreateFavoriteAction
{
    public function execute(FavoriteData $favoriteData): bool
    {
        try {
            Favorite::firstOrCreate([
                'customer_id' => $favoriteData->customer_id,
                'product_id' => $favoriteData->product_id,
            ]);

            return true;
        } catch (Exception) {
            return false;
        }
    }
}
