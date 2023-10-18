<?php

declare(strict_types=1);

use Domain\Admin\Models\Admin;
use Domain\Customer\Models\Customer;
use Domain\PaymentMethod\Models\PaymentMethod;
use Domain\Service\Models\Service;
use Domain\ServiceOrder\Models\ServiceBill;
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
            $table->foreignIdFor(Admin::class)->nullable()->index();

            $table->string('reference')->unique();
            $table->string('customer_first_name');
            $table->string('customer_last_name');
            $table->string('customer_email');
            $table->string('customer_mobile');
            $table->json('customer_form');
            $table->json('additional_charges');
            $table->string('currency_code')->index();
            $table->string('currency_name')->index();
            $table->string('currency_symbol');
            $table->string('service_name');
            $table->decimal('service_price', 10, 2);
            $table->string('billing_cycle')->nullable();
            $table->integer('due_date_every')->nullable();
            $table->dateTime('schedule');
            $table->string('status');
            $table->string('cancelled_reason')->nullable()->default(null);
            $table->decimal('total_price', 10, 2)->index();

            $table->timestamps();
        });

        Schema::create('service_order_addresses', function (Blueprint $table) {
            $table->id();

            $table->foreignIdFor(ServiceOrder::class);
            $table->string('type')->index();
            $table->string('country');
            $table->string('state');
            $table->string('label_as');
            $table->text('address_line_1');
            $table->string('zip_code');
            $table->string('city');

            $table->timestamps();
        });

        Schema::create('service_bills', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(ServiceOrder::class)->index();

            $table->string('reference')->unique();
            $table->dateTime('bill_date')->nullable()->index();
            $table->dateTime('due_date')->nullable()->index();
            $table->decimal('service_price', 10, 2);
            $table->json('additional_charges');
            $table->decimal('total_amount', 10, 2);
            $table->string('status')->index();

            $table->timestamps();
        });

        Schema::create('service_transactions', function (Blueprint $table) {
            $table->id();

            $table->foreignIdFor(ServiceOrder::class)->index();
            $table->foreignIdFor(ServiceBill::class)->index();
            $table->foreignIdFor(PaymentMethod::class)->index();

            $table->string('currency');
            $table->decimal('total_amount', 10, 2)->index();
            $table->string('status')->index();

            $table->timestamps();
        });
    }

    /** Reverse the migrations. */
    public function down(): void
    {
        Schema::dropIfExists('service_transactions');
        Schema::dropIfExists('service_bills');
        Schema::dropIfExists('service_order_addresses');
        Schema::dropIfExists('service_orders');
    }
};
