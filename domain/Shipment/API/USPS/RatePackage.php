<?php

declare(strict_types=1);

namespace Domain\Shipment\API\USPS;

class RatePackage extends Rate
{
    /** @var array - list of all packages added so far */
    protected $packageInfo = [];
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
    /** Container constants */
    public const CONTAINER_VARIABLE = 'VARIABLE';
    public const CONTAINER_FLAT_RATE_ENVELOPE = 'FLAT RATE ENVELOPE';
    public const CONTAINER_PADDED_FLAT_RATE_ENVELOPE = 'PADDED FLAT RATE ENVELOPE';
    public const CONTAINER_LEGAL_FLAT_RATE_ENVELOPE = 'LEGAL FLAT RATE ENVELOPE';
    public const CONTAINER_SM_FLAT_RATE_ENVELOPE = 'SM FLAT RATE ENVELOPE';
    public const CONTAINER_WINDOW_FLAT_RATE_ENVELOPE = 'WINDOW FLAT RATE ENVELOPE';
    public const CONTAINER_GIFT_CARD_FLAT_RATE_ENVELOPE = 'GIFT CARD FLAT RATE ENVELOPE';
    public const CONTAINER_FLAT_RATE_BOX = 'FLAT RATE BOX';
    public const CONTAINER_SM_FLAT_RATE_BOX = 'SM FLAT RATE BOX';
    public const CONTAINER_MD_FLAT_RATE_BOX = 'MD FLAT RATE BOX';
    public const CONTAINER_LG_FLAT_RATE_BOX = 'LG FLAT RATE BOX';
    public const CONTAINER_REGIONALRATEBOXA = 'REGIONALRATEBOXA';
    public const CONTAINER_REGIONALRATEBOXB = 'REGIONALRATEBOXB';
    public const CONTAINER_REGIONALRATEBOXC = 'REGIONALRATEBOXC';
    public const CONTAINER_RECTANGULAR = 'RECTANGULAR';
    public const CONTAINER_NONRECTANGULAR = 'NONRECTANGULAR';
    /** Size constants */
    public const SIZE_LARGE = 'LARGE';
    public const SIZE_REGULAR = 'REGULAR';

    /**
     * Set the service property
     *
     * @param string|int $value
     * @return object RatePackage
     */
    public function setService($value)
    {
        return $this->setField('Service', $value);
    }

    /**
     * Set the first class mail type property
     *
     * @param string|int $value
     * @return object RatePackage
     */
    public function setFirstClassMailType($value)
    {
        return $this->setField('FirstClassMailType', $value);
    }

    /**
     * Set the zip origin property
     *
     * @param string|int $value
     * @return object RatePackage
     */
    public function setZipOrigination($value)
    {
        return $this->setField('ZipOrigination', $value);
    }

    /**
     * Set the zip destination property
     *
     * @param string|int $value
     * @return object RatePackage
     */
    public function setZipDestination($value)
    {
        return $this->setField('ZipDestination', $value);
    }

    /**
     * Set the pounds property
     *
     * @param string|int $value
     * @return object RatePackage
     */
    public function setPounds($value)
    {
        return $this->setField('Pounds', $value);
    }

    /**
     * Set the ounces property
     *
     * @param string|int $value
     * @return object RatePackage
     */
    public function setOunces($value)
    {
        return $this->setField('Ounces', $value);
    }

    /**
     * Set the container property
     *
     * @param string|int $value
     * @return object RatePackage
     */
    public function setContainer($value)
    {
        return $this->setField('Container', $value);
    }

    /**
     * Set the size property
     *
     * @param string|int $value
     * @return object RatePackage
     */
    public function setSize($value)
    {
        return $this->setField('Size', $value);
    }

    /**
     * Set the machinable property
     *
     * @param string|int $value
     * @return object RatePackage
     */
    public function setMachinable($value)
    {
        return $this->setField('Machinable', $value);
    }

    /**
     * Add an element to the stack
     *
     * @param string|int $key
     * @param string|int $value
     * @return object USPSAddress
     */
    public function setField($key, $value)
    {
        $this->packageInfo[ucwords((string) $key)] = $value;

        return $this;
    }

    /**
     * Returns a list of all the info we gathered so far in the current package object
     *
     * @return array
     */
    public function getPackageInfo()
    {
        return $this->packageInfo;
    }
}
