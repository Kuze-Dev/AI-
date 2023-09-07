<?php

declare(strict_types=1);

use Domain\Payments\Models\Payment;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    /** Run the migrations. */
    public function up(): void
    {
        Schema::create('payment_refunds', function (Blueprint $table) {
            $table->id();

            $table->foreignIdFor(Payment::class);

            $table->string('refund_id');
            $table->string('amount');
            $table->string('status');

            $table->string('transaction_id')->nullable();

            $table->longText('remarks')->nullable();
            $table->longText('message')->nullable();

            $table->json('refund_details')->nullable();

            $table->timestamps();
        });
    }

    /** Reverse the migrations. */
    public function down(): void
    {
        Schema::dropIfExists('payment_refunds');
    }
};
