<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('carts', function (Blueprint $table) {
            $table->foreignId('customer_id')->nullable()->change();
            $table->string('session_id')->after('customer_id')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('carts', function (Blueprint $table) {
            $table->foreignId('customer_id')->change();
            $table->dropColumn('session_id');
        });
    }
};
