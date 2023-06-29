<?php

declare(strict_types=1);

use Domain\PaymentMethod\Models\PaymentMethod;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    /** Run the migrations. */
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->morphs('payable');

            $table->foreignIdFor(PaymentMethod::class);

            $table->string('gateway');
            $table->string('currency');
            $table->string('amount');
            $table->string('status');
            $table->string('payment_id')->nullable();
            $table->string('transaction_id')->nullable();

            $table->json('payment_details');

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /** Reverse the migrations. */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
