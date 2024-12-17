<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
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

            $table->float('length')->unsigned();
            $table->float('width')->unsigned();
            $table->float('height')->unsigned();
            $table->float('volume')->unsigned();

            $table->string('weight_units')->nullable();
            $table->float('max_weight')->unsigned()->default(0);

            $table->timestamps();
        });
    }

    /** Reverse the migrations. */
    public function down(): void
    {
        Schema::dropIfExists('shipping_boxes');
    }
};
