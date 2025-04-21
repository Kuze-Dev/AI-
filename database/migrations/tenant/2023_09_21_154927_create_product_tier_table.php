<?php

declare(strict_types=1);

use Domain\Product\Models\Product;
use Domain\Tier\Models\Tier;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /** Run the migrations. */
    public function up(): void
    {
        Schema::create('product_tier', function (Blueprint $table) {
            $table->id();
            $table->decimal('discount', 6, 3)->unsigned()->default(0);
            $table->foreignIdFor(Product::class)->index();
            $table->foreignIdFor(Tier::class)->index();
        });
    }

    /** Reverse the migrations. */
    public function down(): void
    {
        Schema::dropIfExists('product_tier');
    }
};
