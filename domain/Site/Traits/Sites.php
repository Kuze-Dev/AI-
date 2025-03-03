<?php

declare(strict_types=1);

namespace Domain\Site\Traits;

use Domain\Site\Models\Site;

trait Sites
{
    /**
     * Declare relationship of
     * current model to site.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany<\Domain\Site\Models\Site>
     * @phpstan-ignore generics.lessTypes, generics.lessTypes, generics.lessTypes, generics.lessTypes, generics.lessTypes, generics.lessTypes, generics.lessTypes, generics.lessTypes */
    public function sites()
    {
        /** @phpstan-ignore return.type, return.type, return.type, return.type, return.type, return.type, return.type, return.type */
        return $this->morphToMany(Site::class, 'model_sites');
    }
}
