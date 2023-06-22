<?php

declare(strict_types=1);

namespace Domain\Country\Actions;

use Domain\Country\Models\Country;

class DeleteCountryAction
{
    public function execute(Country $country): ?bool
    {
        return $country->delete();
    }
}
