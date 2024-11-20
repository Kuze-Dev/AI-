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
        Schema::table('taxonomies', function (Blueprint $table) {

            $table->dropUnique(['name']);

            $table->string('translation_id')
                ->comment('ID of the parent content on which the translation is based.')
                ->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('taxonomies', function (Blueprint $table) {
            $table->unique('name');
            $table->dropColumn('translation_id');
        });
    }
};
