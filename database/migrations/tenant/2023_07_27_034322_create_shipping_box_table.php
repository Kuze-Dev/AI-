<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    /** Run the migrations. */
    public function up(): void
    {
        Schema::create('shipping_boxes', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('slug')->unique();

            $table->string('package_type')->nullable();

            $table->string('courier');
            $table->string('dimension_units');

            $table->unsignedFloat('length');
            $table->unsignedFloat('width');
            $table->unsignedFloat('height');
            $table->unsignedFloat('volume');

            $table->string('weight_units')->nullable();
            $table->unsignedFloat('max_weight')->default(0);

            $table->timestamps();
        });
    }

    /** Reverse the migrations. */
    public function down(): void
    {
        Schema::dropIfExists('shipping_boxes');
    }
};
