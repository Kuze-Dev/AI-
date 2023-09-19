<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('services', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(\Domain\Blueprint\Models\Blueprint::class);
            $table->string('name');
            $table->string('slug');
            $table->text('description')->nullable();
            $table->integer('price')->nullable();
            $table->boolean('is_featured');
            $table->boolean('is_special_offer');
            $table->boolean('is_subscription');
            $table->enum('status', ['active, inactive']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('services');
    }
};
