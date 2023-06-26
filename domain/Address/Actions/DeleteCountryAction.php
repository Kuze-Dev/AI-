<?php

declare(strict_types=1);

namespace Domain\Address\Actions;

use Domain\Address\Models\Address;

class DeleteCountryAction
{
    public function execute(Country $country): ?bool
    {
        return $country->delete();
    }
}
