<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /** Run the migrations. */
    public function up(): void
    {
        Schema::table('pages', function (Blueprint $table) {
            $table->dropColumn('route_url');
        });

        Schema::table('contents', function (Blueprint $table) {
            $table->dropUnique(['route_url']);
            $table->dropColumn('route_url');
        });
    }

    /** Reverse the migrations. */
    public function down(): void
    {
        Schema::table('pages', function (Blueprint $table) {
            $table->string('route_url')->unique();
        });

        Schema::table('contents', function (Blueprint $table) {
            $table->string('route_url')->unique();
        });
    }
};
