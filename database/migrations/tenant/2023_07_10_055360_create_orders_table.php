<?php

declare(strict_types=1);

use Domain\Customer\Models\Customer;
use Domain\Order\Models\Order;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
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
            $table->string('currency_symbol');
            $table->string('reference')->unique();
            $table->decimal('tax_total')->nullable();
            $table->decimal('tax_percentage')->nullable();
            $table->string('tax_display')->nullable();
            $table->decimal('sub_total');
            $table->decimal('discount_total');
            $table->integer('discount_id')->nullable();
            $table->string('discount_code')->nullable();
            $table->decimal('shipping_total');
            $table->string('shipping_method_id')->index();
            $table->decimal('total')->index();
            $table->longText('notes')->nullable();
            $table->boolean('is_paid')->default(false);
            $table->string('status')->index();
            $table->string('cancelled_reason')->nullable();

            $table->timestamp('cancelled_at')->nullable();
            $table->timestamps();
        });

        Schema::create('order_addresses', function (Blueprint $table) {
            $table->id();

            $table->foreignIdFor(Order::class);
            $table->string('type')->index();
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
            $table->uuid();

            $table->foreignIdFor(Order::class);
            $table->unsignedInteger('purchasable_id');
            $table->string('purchasable_type')->index();
            $table->string('purchasable_sku');
            $table->string('name')->index();
            $table->decimal('unit_price');
            $table->integer('quantity');
            $table->decimal('tax_total')->nullable();
            $table->decimal('tax_percentage')->nullable();
            $table->string('tax_display')->nullable();
            $table->decimal('sub_total');
            $table->decimal('discount_total');
            $table->integer('discount_id')->nullable();
            $table->string('discount_code')->nullable();
            $table->decimal('total')->index();

            $table->json('remarks_data')->nullable();
            $table->json('purchasable_data')->nullable();

            $table->dateTime('reviewed_at')->nullable();
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
