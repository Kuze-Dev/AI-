<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Domain\Product\Models\Product;
use Domain\Customer\Models\Customer;
use Domain\Order\Models\Order;
use Domain\Order\Models\OrderLine;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Product::class)->index();
            $table->foreignIdFor(Order::class)->index();
            $table->foreignIdFor(OrderLine::class)->index();
            $table->foreignIdFor(Customer::class)->nullable()->index();

            $table->smallInteger('rating');
            $table->string('customer_name')->nullable();
            $table->string('customer_email')->nullable();
            $table->string('comment')->nullable();
            $table->boolean('is_anonymous');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reviews');
    }
};
