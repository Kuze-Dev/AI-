<?php

declare(strict_types=1);

use Domain\Customer\Models\Customer;
use Domain\Service\Models\Service;
use Domain\ServiceOrder\Models\ServiceOrder;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    /** Run the migrations. */
    public function up(): void
    {
        Schema::create('service_orders', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Service::class)->index();
            $table->foreignIdFor(Customer::class)->index();

            $table->string('customer_first_name');
            $table->string('customer_last_name');
            $table->string('customer_email');
            $table->string('customer_mobile_no');
            $table->string('service_address');
            $table->string('billing_address');
            $table->string('currency_code')->index();
            $table->string('currency_name')->index();
            $table->string('currency_symbol');
            $table->string('service_name');
            $table->string('service_price');
            $table->dateTime('schedule');
            $table->string('status');
            $table->string('cancelled_reason');
            $table->decimal('total', 10, 2)->index();

            $table->timestamps();
        });

        Schema::create('service_order_additional_charges', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(ServiceOrder::class)->index();

            $table->string('name');
            $table->integer('quantity')->default(1);
            $table->decimal('price', 10, 2);

            $table->timestamps();
        });
    }

    /** Reverse the migrations. */
    public function down(): void
    {
        Schema::dropIfExists('service_orders');
        Schema::dropIfExists('service_order_additional_charges');
    }
};
