<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public $tables = ['pages', 'content_entries', 'menus', 'forms', 'taxonomies', 'globals'];

    /** Run the migrations. */
    public function up(): void
    {
        foreach ($this->tables as $table) {
            Schema::table($table, function (Blueprint $table) {
                $table->string('locale')->default('en')->index();
            });
        }
    }

    /** Reverse the migrations. */
    public function down(): void
    {
        foreach ($this->tables as $table) {
            Schema::table($table, function (Blueprint $table) {
                $table->dropColumn('locale');
            });
        }
    }
};
