<?php

declare(strict_types=1);

namespace Domain\Shipment\Models\Traits;

use Domain\Shipment\Models\Shipment;
use Illuminate\Database\Eloquent\Relations\MorphMany;

trait HasShipments
{
    /** @return MorphMany<Shipment> */
    public function payments(): MorphMany
    {
        return $this->morphMany(Shipment::class, 'model');
    }
}
