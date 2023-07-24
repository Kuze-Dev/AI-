<?php

declare(strict_types=1);

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

            $table->string('driver');
            $table->json('ship_from_address');

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
