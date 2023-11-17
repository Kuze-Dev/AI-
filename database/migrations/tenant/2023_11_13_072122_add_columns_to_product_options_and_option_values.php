<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    /** Run the migrations. */
    public function up(): void
    {
        Schema::table('product_options', function (Blueprint $table) {
            $table->boolean('is_custom')->default(false)->after('slug');
        });

        Schema::table('product_option_values', function (Blueprint $table) {
            $table->json('data')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('product_options', function (Blueprint $table) {
            $table->dropColumn('is_custom');
        });

        Schema::table('product_option_values', function (Blueprint $table) {
            $table->dropColumn('data');
        });
    }
};
