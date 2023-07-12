<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Domain\Product\Models\Product;
use Domain\Customer\Models\Customer;
use phpDocumentor\Reflection\Types\Nullable;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Product::class)->index();
            $table->foreignIdFor(Customer::class)->index();

            $table->string('title')->index();
            $table->smallInteger('rating');
            $table->string('comment')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reviews');
    }
};
