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

            $table->timestamps();
        });

        Schema::create('cart_lines', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Cart::class)->index()->onDelete('cascade');

            $table->unsignedInteger('purchasable_id')->index()->onDelete('cascade');
            $table->string('purchasable_type')->index();;
            $table->unsignedInteger('variant_id')->nullable();
            $table->integer('quantity');
            $table->longText('notes')->nullable();
            $table->boolean("for_check_out")->default(false);

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
