<?php

declare(strict_types=1);

namespace Domain\Shipment\API\USPS\Constants;

final class ServiceType
{
    /** Services constants */
    public const SERVICE_FIRST_CLASS = 'FIRST CLASS';
    public const SERVICE_FIRST_CLASS_COMMERCIAL = 'FIRST CLASS COMMERCIAL';
    public const SERVICE_FIRST_CLASS_HFP_COMMERCIAL = 'FIRST CLASS HFP COMMERCIAL';
    public const SERVICE_PRIORITY = 'PRIORITY';
    public const SERVICE_PRIORITY_COMMERCIAL = 'PRIORITY COMMERCIAL';
    public const SERVICE_PRIORITY_HFP_COMMERCIAL = 'PRIORITY HFP COMMERCIAL';
    public const SERVICE_EXPRESS = 'EXPRESS';
    public const SERVICE_EXPRESS_COMMERCIAL = 'EXPRESS COMMERCIAL';
    public const SERVICE_EXPRESS_SH = 'EXPRESS SH';
    public const SERVICE_EXPRESS_SH_COMMERCIAL = 'EXPRESS SH COMMERCIAL';
    public const SERVICE_EXPRESS_HFP = 'EXPRESS HFP';
    public const SERVICE_EXPRESS_HFP_COMMERCIAL = 'EXPRESS HFP COMMERCIAL';
    public const SERVICE_PARCEL = 'PARCEL';
    public const SERVICE_MEDIA = 'MEDIA';
    public const SERVICE_LIBRARY = 'LIBRARY';
    public const SERVICE_ALL = 'ALL';
    public const SERVICE_ONLINE = 'ONLINE';
}
