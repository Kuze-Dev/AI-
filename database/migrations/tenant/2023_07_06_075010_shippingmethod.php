<?php

declare(strict_types=1);

use Domain\Address\Models\Country;
use Domain\Address\Models\State;
use Domain\ShippingMethod\Models\ShippingMethod;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('shipping_methods', function (Blueprint $table) {

            $table->id();

            $table->string('title')->unique();
            $table->string('slug')->unique();

            $table->string('subtitle')->nullable();
            $table->string('description')->nullable();

            $table->foreignIdFor(Country::class, 'shipper_country_id')->index();
            $table->foreignIdFor(State::class, 'shipper_state_id')->index();

            $table->string('shipper_address');
            $table->string('shipper_city');
            $table->string('shipper_zipcode');

            $table->string('driver');

            $table->boolean('active');

            $table->timestamps();
            $table->softDeletes();

        });

        Schema::create('shipments', function (Blueprint $table) {

            $table->id();
            $table->morphs('model');

            $table->foreignIdFor(ShippingMethod::class)->index();

            $table->string('tracking_id')->nullable();
            $table->string('status')->nullable();
            $table->string('rate');

            $table->json('shipping_details')->nullable();
            $table->json('destination_address')->nullable();

            $table->timestamps();

        });

    }

    public function down(): void
    {
        Schema::dropIfExists('shipping_methods');
        Schema::dropIfExists('shipments');
    }
};
