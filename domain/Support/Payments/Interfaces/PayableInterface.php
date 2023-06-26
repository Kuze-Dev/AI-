<?php

namespace Domain\Support\Payments\Interfaces;

use Illuminate\Database\Eloquent\Relations\MorphMany;

interface PayableInterface
{
    public function payments(): MorphMany;
}
