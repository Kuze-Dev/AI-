<?php

declare(strict_types=1);

namespace Domain\Shipment\API\USPS\Enums;

enum MailType: string
{
    /**
     * First class mail type
     * required when you use one of the first class services
     */
    case LETTER = 'LETTER';
    case FLAT = 'FLAT';
    case PARCEL = 'PARCEL';
    case POSTCARD = 'POSTCARD';
    case PACKAGE = 'Package';
    case PACKAGE_SERVICE = 'PACKAGE SERVICE';
}
