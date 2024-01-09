<?php

declare(strict_types=1);

use Domain\ServiceOrder\Models\ServiceBill;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /** Run the migrations. */
    public function up(): void
    {
        Schema::table('service_transactions', function (Blueprint $table) {
            $table->foreignIdFor(ServiceBill::class)->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('service_transactions', function (Blueprint $table) {
            $table->foreignIdFor(ServiceBill::class)->change();
        });
    }
};
