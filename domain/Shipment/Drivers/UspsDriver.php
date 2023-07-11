<?php

declare(strict_types=1);

namespace Domain\Shipment\Drivers;

use App\Settings\ShippingSettings;
use Domain\Shipment\API\USPS\Rate;
use Domain\Shipment\API\USPS\RatePackage;

class UspsDriver
{
    protected string $name = 'usps';

    protected ?array $uspsCredentials = null;

    public function __construct()
    {
        $this->uspsCredentials = app(ShippingSettings::class)->usps_credentials;

    }

    public function handle(): void
    {

    }

    public function getRate(): array
    {

        $rate = new Rate($this->uspsCredentials['username']);

        $rate->setPassword($this->uspsCredentials['password']);
        $rate->addExtraOption('Revision','1');
        $rate->setInternationalCall(true);   
        $package = new RatePackage();
        // $package->setService(RatePackage::SERVICE_PRIORITY);

        // $package->setFirstClassMailType(RatePackage::CONTAINER_VARIABLE);

        // $package->setZipOrigination('91601');
        // $package->setZipDestination('91730');
        $package->setPounds('10');
        $package->setOunces('3.5');
        // $package->setContainer('');
        // $package->setSize(RatePackage::SIZE_REGULAR);
        $package->setField('Machinable', true);

        //international rates

        $package->setContainer('VARIABLE');

        $package->setField('Container','VARIABLE')
        ->setField('MailType',RatePackage::MAIL_TYPE_PACKAGE)
        ->setField('ValueOfContents','200')
        ->setField('Country','Australia')
        ->setField('Width','10')
        ->setField('Length','15')
        ->setField('Height','10')
        ->setField('CommercialFlag','N')
        ->setField('AcceptanceDateTime','2023-07-11T13:15:00-06:00')
        ->setField('DestinationPostalCode','2046')
        ->setField('OriginZip','18701')
     
        ;
        /**
         * 
         *   <CommercialFlag>N</CommercialFlag>
         *  <DestinationPostalCode>2046</DestinationPostalCode>
         */
        // add the package to the rate stack
        // $rate->setInternationalCall(true);
        // $rate->addExtraOption('Pounds', '123');
        // $rate->addExtraOption('Ounces', '123');
        // $rate->addExtraOption('Machinable', 'true');
        // $rate->addExtraOption('MailType', 'Package');

        $rate->addPackage($package);
     
        $rate->getRate();

        return $rate->convertResponseToArray();
    }
}
