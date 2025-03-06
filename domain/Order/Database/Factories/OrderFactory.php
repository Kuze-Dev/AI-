<?php

declare(strict_types=1);

namespace Domain\Order\Database\Factories;

use Domain\Address\Enums\AddressLabelAs;
use Domain\Discount\Actions\AutoGenerateCode;
use Domain\Order\Enums\OrderAddressTypes;
use Domain\Order\Enums\OrderStatuses;
use Domain\Order\Models\Order;
use Domain\Product\Database\Factories\ProductFactory;
use Domain\Product\Models\Product;
use Domain\Taxation\Enums\PriceDisplay;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\Domain\Order\Models\Order>
 */
class OrderFactory extends Factory
{
    protected $model = Order::class;

    #[\Override]
    public function definition(): array
    {
        return [
            'customer_id' => 1,
            'customer_first_name' => $this->faker->firstName(),
            'customer_last_name' => $this->faker->lastName(),
            'customer_mobile' => $this->faker->phoneNumber(),
            'customer_email' => $this->faker->unique()->safeEmail(),

            'currency_code' => $this->faker->unique()->word(),
            'currency_name' => $this->faker->unique()->name(),
            'currency_symbol' => $this->faker->unique()->name(),

            'reference' => $this->faker->unique()->randomElement([
                Str::upper(Str::random(12)),
            ]),

            'tax_total' => $this->faker->randomFloat(2, 0, 10),
            'tax_display' => Arr::random(PriceDisplay::cases()),
            'tax_percentage' => $this->faker->randomFloat(2, 0, 100),

            'sub_total' => $this->faker->randomFloat(2, 0, 100),

            'discount_total' => $this->faker->randomFloat(2, 0, 10),
            'discount_id' => $this->faker->unique()->numberBetween(1, 5),
            'discount_code' => new AutoGenerateCode,

            'shipping_total' => $this->faker->randomFloat(2, 0, 10),
            'shipping_method_id' => 1,

            'total' => $this->faker->randomFloat(2, 0, 100),

            'notes' => null,
            'status' => OrderStatuses::PENDING,
            'is_paid' => false,
        ];
    }

    #[\Override]
    public function configure()
    {
        return $this->afterCreating(function (Order $order) {
            $order->orderLines()->create([
                'uuid' => (string) Str::uuid(),
                'order_id' => $order->id,
                'purchasable_id' => $this->faker->numberBetween(1, 5),
                'purchasable_type' => Product::class,
                'purchasable_sku' => $this->faker->unique()->numerify('SKU###'),
                'name' => $this->faker->name,
                'unit_price' => $this->faker->randomFloat(2, 0, 100),
                'quantity' => $this->faker->numberBetween(1, 5),
                'tax_total' => $this->faker->randomFloat(2, 0, 10),
                'tax_display' => Arr::random(PriceDisplay::cases()),
                'tax_percentage' => $this->faker->randomFloat(2, 0, 100),
                'sub_total' => $this->faker->randomFloat(2, 0, 100),
                'discount_total' => 0,
                'total' => $this->faker->randomFloat(2, 0, 100),
                'remarks_data' => null,
                'purchasable_data' => ProductFactory::new()->createOne(),
            ]);

            $order->shippingAddress()->create([
                'order_id' => $order->id,
                'country' => 'Philippines',
                'state' => 'Pampanga',
                'type' => OrderAddressTypes::SHIPPING,
                'label_as' => AddressLabelAs::HOME,
                'address_line_1' => '855 Proper San Jose',
                'zip_code' => '2014',
                'city' => 'San Luis',
            ]);

            $order->billingAddress()->create([
                'order_id' => $order->id,
                'country' => 'Philippines',
                'state' => 'Pampanga',
                'type' => OrderAddressTypes::BILLING,
                'label_as' => AddressLabelAs::HOME,
                'address_line_1' => '855 Proper San Jose',
                'zip_code' => '2014',
                'city' => 'San Luis',
            ]);
        });
    }
}
