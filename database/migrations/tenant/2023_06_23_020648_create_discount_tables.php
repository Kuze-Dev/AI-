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

            $table->string('name')->index();
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->string('code')->unique();
            $table->string('status')->index();
            $table->unsignedInteger('max_uses')->nullable();
            // $table->unsignedInteger('max_uses_per_user');

            $table->timestamp('valid_start_at');
            $table->timestamp('valid_end_at')->nullable();
            $table->softDeletes('deleted_at')->nullable();
            $table->timestamps();
        });

        // Schema::create('discount_codes', function (Blueprint $table) {
        //     $table->id();

        //     $table->foreignIdFor(Discount::class)->index();
        //     // $table->foreignIdFor(Customer::class);

        //     $table->string('code')->unique();

        //     $table->timestamps();
        // });

        Schema::create('discount_conditions', function (Blueprint $table) {
            $table->id();

            $table->foreignIdFor(Discount::class)->index();

            $table->string('discount_type')->index();
            $table->string('amount_type')->index();
            $table->bigInteger('amount');

            $table->timestamps();
        });

        Schema::create('discount_requirements', function (Blueprint $table) {
            $table->id();

            $table->foreignIdFor(Discount::class)->index();

            $table->string('requirement_type')
                ->nullable()
                ->index();
            $table->bigInteger('minimum_amount')
                ->nullable();

            $table->timestamps();
        });

    }

    public function down(): void
    {
        Schema::dropIfExists('discount_requirements');
        Schema::dropIfExists('discount_conditions');
        // Schema::dropIfExists('discount_codes');
        Schema::dropIfExists('discounts');
    }
};
