<?php

declare(strict_types=1);

namespace Domain\Support\Payments\Interfaces;

use Illuminate\Database\Eloquent\Relations\MorphMany;

interface PayableInterface
{
    /** @return MorphMany<\Domain\Support\Payments\Models\Payment> */
    public function payments(): MorphMany;
}
