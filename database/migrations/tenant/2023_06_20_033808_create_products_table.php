<?php

declare(strict_types=1);

use Domain\Product\Models\Product;
use Domain\Product\Models\ProductOption;
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
            $table->json('dimension')->nullable();
            $table->decimal('weight')->nullable();
            $table->unsignedInteger('stock')->nullable();
            $table->mediumText('description')->nullable();
            $table->boolean('status')->default(true);
            $table->boolean('is_digital_product')->default(false);
            $table->boolean('is_featured')->default(false);
            $table->boolean('is_special_offer')->default(false);
            $table->boolean('allow_customer_remarks')->default(false);
            $table->boolean('allow_stocks')->default(true);
            $table->unsignedInteger('minimum_order_quantity')->default(1);
            $table->timestamps();
        });

        Schema::create('product_taxonomy_term', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Product::class)->index();
            $table->foreignIdFor(TaxonomyTerm::class)->index();
        });

        Schema::create('product_variants', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Product::class)->index();
            $table->string('sku')->unique();
            $table->json('combination');
            $table->decimal('retail_price', 10, 2);
            $table->decimal('selling_price', 10, 2);
            $table->unsignedInteger('stock')->nullable();
            $table->boolean('status')->default(true);
            $table->timestamps();
        });

        Schema::create('product_options', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Product::class)->index();
            $table->string('name');
            $table->string('slug');
            $table->timestamps();
        });

        Schema::create('product_option_values', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug');
            $table->foreignIdFor(ProductOption::class)->index();
        });
    }

    /** Reverse the migrations. */
    public function down(): void
    {
        Schema::dropIfExists('products');
        Schema::dropIfExists('product_taxonomy_term');
        Schema::dropIfExists('product_variants');
        Schema::dropIfExists('product_options');
        Schema::dropIfExists('product_option_values');
    }
};
