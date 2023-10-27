<?php

declare(strict_types=1);

use Domain\Cart\Models\Cart;
use Domain\Customer\Models\Customer;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('carts', function (Blueprint $table) {
            $table->id();

            $table->foreignIdFor(Customer::class)->index();
            $table->uuid();
            $table->string('coupon_code')->index()->nullable();

            $table->timestamps();
        });

        Schema::create('cart_lines', function (Blueprint $table) {
            $table->id();

            $table->foreignIdFor(Cart::class)->index();
            $table->morphs('purchasable');

            $table->uuid();
            $table->integer('quantity');
            $table->json('remarks')->nullable();
            $table->string('checkout_reference')->nullable();

            $table->dateTime('checkout_expiration')->nullable();
            $table->dateTime('checked_out_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cart_lines');
        Schema::dropIfExists('carts');
    }
};
