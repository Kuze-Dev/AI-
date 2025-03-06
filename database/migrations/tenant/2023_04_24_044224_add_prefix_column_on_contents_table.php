<?php

declare(strict_types=1);

use Domain\Content\Models\Content;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /** Run the migrations. */
    public function up(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            Schema::table('contents', function (Blueprint $table) {
                $table->string('prefix')->nullable()->after('slug');
            });

            // WORKAROUND: sqlite not allowing to add a NOT NULL column. https://stackoverflow.com/q/3170634
            Schema::table('contents', function (Blueprint $table) {
                $table->string('prefix')->nullable(false)->change();
            });
        } else {
            Schema::table('contents', function (Blueprint $table) {
                $table->string('prefix')->after('slug');
            });
        }

        DB::table((new Content)->getTable())
            ->oldest('id')
            ->lazy()
            ->each(
                fn ($row) => DB::table((new Content)->getTable())
                    ->where('id', $row->id)
                    ->update(['prefix' => $row->slug])
            );

        Schema::table('contents', function (Blueprint $table) {
            $table->unique('prefix');
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
