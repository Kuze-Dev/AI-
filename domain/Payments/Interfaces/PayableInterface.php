<?php

declare(strict_types=1);

namespace Domain\Payments\Interfaces;

use Illuminate\Database\Eloquent\Relations\MorphMany;

interface PayableInterface
{
    /**
     * @return MorphMany<\Domain\Payments\Models\Payment>
     *
     * @phpstan-ignore generics.lessTypes */
    public function payments(): MorphMany;

    public function getReferenceNumber(): string;
}
