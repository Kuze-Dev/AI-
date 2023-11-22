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
        Schema::create('point_earnings', function (Blueprint $table) {
            $table->id();
            $table->morphs('customer');
            $table->morphs('order');
            $table->decimal('earned_points', 8, 2);
            $table->dateTime('date_earned');
            $table->timestamps();
        });

    }

    /** Reverse the migrations. */
    public function down(): void
    {
        Schema::dropIfExists('point_earnings');
    }
};
