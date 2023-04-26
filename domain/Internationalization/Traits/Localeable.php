<?php

declare(strict_types=1);

namespace Domain\Locale\Traits;

use Domain\Locale\Models\Locale;

trait Localeable
{
    /**
     * Declare relationship of
     * current model to Locale.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany<\Domain\Locale\Models\Locale>
     */
    public function locales()
    {
        return $this->morphToMany(Locale::class, 'model_locales');
    }
}
