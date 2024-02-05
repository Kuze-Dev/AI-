<?php

declare(strict_types=1);

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
        Schema::table('service_orders', function (Blueprint $table) {
            $table->string('payment_type')->nullable()->after('total_price');
            $table->string('payment_value')->nullable()->after('payment_type');
            $table->json('payment_plan')->nullable()->after('payment_value');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('service_orders', function (Blueprint $table) {
            $table->dropColumn('payment_type');
            $table->dropColumn('payment_value');
            $table->dropColumn('payment_plan');
        });
    }
};
