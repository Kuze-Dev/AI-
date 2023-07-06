<?php

declare(strict_types=1);

use Domain\Cart\Models\Cart;
use Domain\Customer\Models\Customer;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::create('carts', function (Blueprint $table) {
            $table->id();

            $table->foreignIdFor(Customer::class)->index()->onDelete('cascade');
            $table->string('coupon_code')->nullable()->default(null)->index();

            $table->timestamps();
        });

        Schema::create('cart_lines', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Cart::class)->index()->onDelete('cascade');

            $table->unsignedInteger('purchasable_id')->index()->onDelete('cascade');
            $table->string('purchasable_type')->index();
            $table->integer('quantity');
            $table->json('meta')->nullable()->default(null);
            $table->string('checkout_reference')->nullable()->default(null);

            $table->dateTime("checkout_expiration")->nullable()->default(null);
            $table->dateTime("checked_out_at")->nullable()->default(null);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cart_lines');
        Schema::dropIfExists('carts');
    }
};
