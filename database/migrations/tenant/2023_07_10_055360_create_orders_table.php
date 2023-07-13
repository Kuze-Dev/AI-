<?php

declare(strict_types=1);

use Domain\Customer\Models\Customer;
use Domain\Order\Models\Order;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();

            $table->foreignIdFor(Customer::class);
            $table->string('customer_first_name');
            $table->string('customer_last_name');
            $table->string('customer_mobile')->index();
            $table->string('customer_email')->index();
            $table->string('currency_code')->index();
            $table->string('currency_name')->index();
            $table->decimal('currency_exchange_rate');
            $table->string('reference')->unique();
            $table->decimal('tax_total');
            $table->decimal('sub_total');
            $table->decimal('discount_total');
            $table->decimal('shipping_total');
            $table->decimal('total')->index();
            $table->longText('notes')->nullable();
            $table->string('shipping_method')->index();
            $table->string('shipping_details');
            $table->string('payment_method')->index();
            $table->string('payment_details');
            $table->enum('payment_status', ['Approved', 'Declined'])->nullable();
            $table->text('payment_message')->nullable();
            $table->boolean('is_paid')->default(false);
            $table->enum('status', [
                'Pending', 'Cancelled', 'For Cancellation', 'Refunded', 'Packed', 'Shipped', 'Delivered', 'Fulfilled',
            ])->default('Pending')->index();
            $table->string('cancelled_reason')->nullable();

            $table->timestamp('cancelled_at')->nullable();
            $table->timestamps();
        });

        Schema::create('order_addresses', function (Blueprint $table) {
            $table->id();

            $table->foreignIdFor(Order::class);
            $table->enum('type', ['Shipping', 'Billing'])->index();
            $table->string('country');
            $table->string('state');
            $table->string('label_as');
            $table->text('address_line_1');
            $table->string('zip_code');
            $table->string('city');

            $table->timestamps();
        });

        Schema::create('order_lines', function (Blueprint $table) {
            $table->id();

            $table->foreignIdFor(Order::class);
            $table->unsignedInteger('purchasable_id');
            $table->string('purchasable_type')->index();
            $table->string('purchasable_sku');
            $table->string('name')->index();
            $table->decimal('unit_price');
            $table->integer('quantity');
            $table->decimal('tax_total');
            $table->decimal('sub_total');
            $table->decimal('discount_total');
            $table->decimal('total')->index();

            $table->json('remarks_data')->nullable();
            $table->json('purchasable_data')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_lines');
        Schema::dropIfExists('order_addresses');
        Schema::dropIfExists('orders');
    }
};
