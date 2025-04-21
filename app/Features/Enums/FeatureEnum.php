<?php

declare(strict_types=1);

namespace App\Features\Enums;

enum FeatureEnum: string
{
    case SHIPPING = 'Shipping';
    case PAYMENTS = 'Payments';

}
