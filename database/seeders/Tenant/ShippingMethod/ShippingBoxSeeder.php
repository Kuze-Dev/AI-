<?php

declare(strict_types=1);

namespace Database\Seeders\Tenant\ShippingMethod;

use Domain\ShippingMethod\Enums\Driver;
use Illuminate\Database\Seeder;
use Domain\Shipment\Enums\BoxTypeEnum;
use Domain\Shipment\Enums\UnitEnum;
use Domain\Shipment\Models\ShippingBox;

class ShippingBoxSeeder extends Seeder
{
    public function run(): void
    {

        foreach ($this->data() as $box) {
            ShippingBox::create($box);
        }

    }

    private function data(): array
    {

        return [

            [
                'name' => 'Mail Flat Rate Small Box',
                'dimension_units' => UnitEnum::INCH,
                'package_type' => BoxTypeEnum::BOX,
                'courier' => Driver::USPS,
                'length' => 8.6,
                'width' => 5.3,
                'height' => 1.6,
                'volume' => 72,
                'weight_units' => UnitEnum::LBS,
            ],

            [
                'name' => 'Mail Flat Rate Medium Box',
                'dimension_units' => UnitEnum::INCH,
                'package_type' => BoxTypeEnum::BOX,
                'courier' => Driver::USPS,
                'length' => 11,
                'width' => 8.5,
                'height' => 5.5,
                'volume' => 514,
                'weight_units' => UnitEnum::LBS,

            ],

            [
                'name' => 'Mail Flat Rate Large Box',
                'dimension_units' => UnitEnum::INCH,
                'package_type' => BoxTypeEnum::BOX,
                'courier' => Driver::USPS,
                'length' => 12,
                'width' => 11.75,
                'height' => 5.5,
                'volume' => 775,
                'weight_units' => UnitEnum::LBS,

            ],

            [
                'name' => 'USPS Customize Box 1',
                'dimension_units' => UnitEnum::INCH,
                'package_type' => BoxTypeEnum::BOX,
                'courier' => Driver::USPS,
                'length' => 12,
                'width' => 12,
                'height' => 6,
                'volume' => 864,
                'weight_units' => UnitEnum::LBS,

            ],

            [
                'name' => 'USPS Customize Box 2',
                'dimension_units' => UnitEnum::INCH,
                'package_type' => BoxTypeEnum::BOX,
                'courier' => Driver::USPS,
                'length' => 12,
                'width' => 12,
                'height' => 10,
                'volume' => 1440,
                'weight_units' => UnitEnum::LBS,

            ],

            [
                'name' => 'USPS Customize Box 3',
                'dimension_units' => UnitEnum::INCH,
                'package_type' => BoxTypeEnum::BOX,
                'courier' => Driver::USPS,
                'length' => 14,
                'width' => 14,
                'height' => 12,
                'volume' => 2352,
                'weight_units' => UnitEnum::LBS,

            ],

            [
                'name' => 'Small ExpressBox',
                'dimension_units' => UnitEnum::INCH,
                'package_type' => BoxTypeEnum::BOX,
                'courier' => Driver::UPS,
                'length' => 13,
                'width' => 11,
                'height' => 2,
                'volume' => 286,
                'weight_units' => UnitEnum::LBS,

            ],

            [
                'name' => 'Medium ExpressBox',
                'dimension_units' => UnitEnum::INCH,
                'package_type' => BoxTypeEnum::BOX,
                'courier' => Driver::UPS,
                'length' => 15,
                'width' => 11,
                'height' => 13,
                'volume' => 2145,
                'weight_units' => UnitEnum::LBS,

            ],

            [
                'name' => 'Large ExpressBox',
                'dimension_units' => UnitEnum::INCH,
                'package_type' => BoxTypeEnum::BOX,
                'courier' => Driver::UPS,
                'length' => 18,
                'width' => 13,
                'height' => 13,
                'volume' => 495,
                'weight_units' => UnitEnum::LBS,

            ],

            [
                'name' => 'UPS Customize Box 1',
                'dimension_units' => UnitEnum::INCH,
                'package_type' => BoxTypeEnum::BOX,
                'courier' => Driver::UPS,
                'length' => 6,
                'width' => 6,
                'height' => 6,
                'volume' => 216,
                'weight_units' => UnitEnum::LBS,

            ],

            [
                'name' => 'UPS Customize Box 2',
                'dimension_units' => UnitEnum::INCH,
                'package_type' => BoxTypeEnum::BOX,
                'courier' => Driver::UPS,
                'length' => 6,
                'width' => 6,
                'height' => 48,
                'volume' => 1728,
                'weight_units' => UnitEnum::LBS,

            ],

            [
                'name' => 'UPS Customize Box 3',
                'dimension_units' => UnitEnum::INCH,
                'package_type' => BoxTypeEnum::BOX,
                'courier' => Driver::UPS,
                'length' => 8,
                'width' => 8,
                'height' => 8,
                'volume' => 512,
                'weight_units' => UnitEnum::LBS,

            ],

            [
                'name' => 'UPS Customize Box 4',
                'dimension_units' => UnitEnum::INCH,
                'package_type' => BoxTypeEnum::BOX,
                'courier' => Driver::UPS,
                'length' => 10,
                'width' => 10,
                'height' => 10,
                'volume' => 1000,
                'weight_units' => UnitEnum::LBS,

            ],

            [
                'name' => 'UPS Customize Box 5',
                'dimension_units' => UnitEnum::INCH,
                'package_type' => BoxTypeEnum::BOX,
                'courier' => Driver::UPS,
                'length' => 12,
                'width' => 12,
                'height' => 6,
                'volume' => 864,
                'weight_units' => UnitEnum::LBS,

            ],

            [
                'name' => 'UPS Customize Box 6',
                'dimension_units' => UnitEnum::INCH,
                'package_type' => BoxTypeEnum::BOX,
                'courier' => Driver::UPS,
                'length' => 12,
                'width' => 12,
                'height' => 12,
                'volume' => 1728,
                'weight_units' => UnitEnum::LBS,

            ],

            [
                'name' => 'UPS Customize Box 7',
                'dimension_units' => UnitEnum::INCH,
                'package_type' => BoxTypeEnum::BOX,
                'courier' => Driver::UPS,
                'length' => 14,
                'width' => 14,
                'height' => 14,
                'volume' => 2744,
                'weight_units' => UnitEnum::LBS,

            ],
        ];

    }
}
