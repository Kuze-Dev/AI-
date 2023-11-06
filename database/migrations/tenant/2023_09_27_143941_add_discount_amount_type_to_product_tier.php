<?php

declare(strict_types=1);

use Domain\Product\Enums\DiscountAmountType;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /** Run the migrations. */
    public function up(): void
    {
        Schema::table('product_tier', function (Blueprint $table) {
            $table->string('discount_amount_type')
                ->after('id')
                ->default(DiscountAmountType::PERCENTAGE->value);
        });
    }

    /** Reverse the migrations. */
    public function down(): void
    {
        Schema::table('product_tier', function (Blueprint $table) {
            $table->dropColumn('discount_amount_type');
        });
    }
};
