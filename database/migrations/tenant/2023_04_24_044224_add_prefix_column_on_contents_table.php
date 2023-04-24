<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    /** Run the migrations. */
    public function up(): void
    {
        Schema::table('contents', function (Blueprint $table) {
            $table->string('prefix')->nullable()->unique();
        });

        // WORKAROUND: sqlite not allowing to add a NOT NULL column. https://stackoverflow.com/q/3170634
        Schema::table('contents', function (Blueprint $table) {
            $table->string('prefix')->nullable(false)->change();
        });
    }

    /** Reverse the migrations. */
    public function down(): void
    {
        Schema::table('contents', function (Blueprint $table) {
            $table->dropColumn('prefix');
        });
    }
};
