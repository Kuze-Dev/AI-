<?php

declare(strict_types=1);

use Domain\Service\Models\Service;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    /** Run the migrations. */
    public function up(): void
    {
        Schema::table('services', function (Blueprint $table) {
            $table->uuid()->after('id');
        });

        DB::table((new Service())->getTable())
            ->orderBy('id')
            ->lazy()
            ->each(
                fn ($row) => DB::table((new Service())->getTable())
                    ->where('id', $row->id)
                    ->update(['uuid' => (string) \Illuminate\Support\Str::uuid()])
            );
    }

    /** Reverse the migrations. */
    public function down(): void
    {
        Schema::table('services', function (Blueprint $table) {
            $table->dropColumn('uuid');
        });
    }
};
