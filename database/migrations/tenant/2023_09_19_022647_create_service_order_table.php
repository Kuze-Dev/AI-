<?php

declare(strict_types=1);

use Domain\Admin\Models\Admin;
use Domain\Customer\Models\Customer;
use Domain\Service\Models\Service;
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
            $table->foreignIdFor(Admin::class, 'created_by')
                ->comment('if not null means created by admin.')
                ->nullable()
                ->index();

            $table->string('customer_first_name');
            $table->string('customer_last_name');
            $table->string('customer_email');
            $table->string('customer_mobile_no');
            $table->json('customer_form');
            $table->json('additional_charges');
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
            $table->decimal('total_price', 10, 2)->index();

            $table->timestamps();
        });

    }

    /** Reverse the migrations. */
    public function down(): void
    {
        Schema::dropIfExists('service_orders');
    }
};
