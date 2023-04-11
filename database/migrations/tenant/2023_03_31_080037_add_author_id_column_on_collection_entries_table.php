<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Domain\Admin\Models\Admin;

return new class () extends Migration {
    /** Run the migrations. */
    public function up(): void
    {
        Schema::table('collection_entries', function (Blueprint $table) {
            $table->foreignIdFor(Admin::class, 'author_id');
        });
    }

    /** Reverse the migrations. */
    public function down(): void
    {
        Schema::table('collection_entries', function (Blueprint $table) {
            $table->dropColumn('author_id');
        });
    }
};
