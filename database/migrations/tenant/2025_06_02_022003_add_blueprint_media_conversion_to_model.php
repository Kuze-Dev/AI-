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
        Schema::table('blueprint_data', function (Blueprint $table) {

            $table->json('blueprint_media_conversion')->nullable()->after('state_path');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('blueprint_data', function (Blueprint $table) {
            $table->drop('blueprint_media_conversion');
        });
    }
};
