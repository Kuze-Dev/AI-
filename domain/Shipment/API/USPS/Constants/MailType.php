<?php

declare(strict_types=1);

namespace Domain\Shipment\API\USPS\Constants;

final class MailType
{
    /**
     * First class mail type
     * required when you use one of the first class services
     */
    public const MAIL_TYPE_LETTER = 'LETTER';
    public const MAIL_TYPE_FLAT = 'FLAT';
    public const MAIL_TYPE_PARCEL = 'PARCEL';
    public const MAIL_TYPE_POSTCARD = 'POSTCARD';
    public const MAIL_TYPE_PACKAGE = 'PACKAGE';
    public const MAIL_TYPE_PACKAGE_SERVICE = 'PACKAGE SERVICE';
}
