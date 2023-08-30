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
     */
    public function sites()
    {
        return $this->morphToMany(Site::class, 'model_sites');
    }
}
