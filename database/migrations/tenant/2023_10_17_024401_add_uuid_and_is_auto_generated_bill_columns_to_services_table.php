<?php

declare(strict_types=1);

use Domain\Service\Enums\BillingCycleEnum;
use Domain\Service\Models\Service;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /** Run the migrations. */
    public function up(): void
    {
        Schema::table('services', function (Blueprint $table) {
            $table->uuid()->nullable()->after('id');
            $table->boolean('is_auto_generated_bill')->default(false)->after('needs_approval');
        });

        DB::table((new Service)->getTable())
            ->orderBy('id')
            ->lazy()
            ->each(
                fn ($row) => DB::table((new Service)->getTable())
                    ->where('id', $row->id)
                    ->update([
                        'uuid' => (string) \Illuminate\Support\Str::uuid(),
                        'due_date_every' => $row->billing_cycle === BillingCycleEnum::YEARLY->value
                            ? min($row->due_date_every, 12) : $row->due_date_every,
                    ])
            );
    }

    /** Reverse the migrations. */
    public function down(): void
    {
        Schema::table('services', function (Blueprint $table) {
            $table->dropColumn('uuid');
            $table->dropColumn('is_auto_generated_bill');
        });
    }
};
