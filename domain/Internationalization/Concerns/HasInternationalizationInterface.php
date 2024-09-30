<?php

declare(strict_types=1);

namespace Domain\Internationalization\Concerns;

use Illuminate\Database\Eloquent\Relations\HasMany;

interface HasInternationalizationInterface
{
    /** @phpstan-ignore-next-line */
    public function dataTranslation(): HasMany;
}
