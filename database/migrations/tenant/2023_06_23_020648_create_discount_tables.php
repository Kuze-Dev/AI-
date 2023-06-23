<?php

declare(strict_types=1);

use Domain\Discount\Models\Discount;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('discounts', function (Blueprint $table) {
            $table->id();

            $table->string('slug')->unique();
            $table->string('name')->index();
            $table->text('description')->nullable();
            $table->string('type')->index();
            $table->string('status')->index();
            $table->unsignedInteger('max_uses');
            $table->unsignedInteger('max_uses_per_user');

            $table->timestamp('valid_start_at');
            $table->timestamp('valid_end_at')->nullable();
            $table->timestamps();
        });

        Schema::create('discount_codes', function (Blueprint $table) {
            $table->id();

            $table->foreignIdFor(Discount::class)->index();
            // $table->foreignIdFor(Customer::class);

            $table->string('code')->unique();

            $table->timestamps();
        });

        Schema::create('discount_conditions', function (Blueprint $table) {
            $table->id();

            $table->foreignIdFor(Discount::class)->index();

            $table->string('type')->index();
            $table->json('data')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('discount_conditions');
        Schema::dropIfExists('discount_codes');
        Schema::dropIfExists('discounts');
    }
};
