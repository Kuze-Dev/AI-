<?php

declare(strict_types=1);

namespace Domain\Payments\Interfaces;

use Illuminate\Database\Eloquent\Relations\MorphMany;

interface PayableInterface
{
    /** @return MorphMany<\Domain\Payments\Models\Payment> */
    public function payments(): MorphMany;
}
