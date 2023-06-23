<?php

declare(strict_types=1);

use Domain\Product\Models\Product;
use Domain\Taxonomy\Models\TaxonomyTerm;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    /** Run the migrations. */
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('slug')->unique();
            $table->string('sku')->unique();
            $table->decimal('retail_price', 10, 2);
            $table->decimal('selling_price', 10, 2);
            $table->decimal('shipping_fee', 10, 2)->nullable();
            $table->unsignedInteger('stock');
            $table->mediumText('description')->nullable();
            $table->string('status')->default(true);
            $table->boolean('is_digital_product')->default(false);
            $table->boolean('is_featured')->default(false);
            $table->boolean('is_special_offer')->default(false);
            $table->boolean('allow_customer_remarks')->default(false);
            $table->boolean('allow_remark_with_image')->default(false);
            $table->timestamps();
        });

        Schema::create('product_taxonomy_term', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Product::class)->index();
            $table->foreignIdFor(TaxonomyTerm::class)->index();
        });
    }

    /** Reverse the migrations. */
    public function down(): void
    {
        Schema::dropIfExists('products');
        Schema::dropIfExists('product_taxonomy_term');
    }
};
