<?php

declare(strict_types=1);

use Domain\Customer\Models\Customer;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('verified_addresses', function (Blueprint $table) {
            $table->id();

            $table->foreignIdFor(Customer::class)->index();

            $table->json('address');
            $table->json('verified_address')->nullable();

            $table->timestamps();

            //            $table->unique(
            //                ['customer_id', 'address', 'verified_address'],
            //                'uq_v_address');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('verified_addresses');
    }
};
