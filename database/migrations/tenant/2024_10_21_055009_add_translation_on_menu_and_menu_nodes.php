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
        Schema::table('menus', function (Blueprint $table) {
            $table->string('translation_id')
                ->comment('ID of the parent menu on which the translation is based.')
                ->nullable();
        });

        Schema::table('nodes', function (Blueprint $table) {
            $table->string('translation_id')
                ->comment('ID of the parent node in which the translation is based.')
                ->nullable();
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('menus', function (Blueprint $table) {
            $table->dropColumn('translation_id');
        });

        Schema::table('nodes', function (Blueprint $table) {
            $table->dropColumn('translation_id');
        });
    }
};
