<?php

declare(strict_types=1);

use Domain\Customer\Models\Customer;
use Domain\Review\Models\Review;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('review_likes', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Review::class)->index();
            $table->foreignIdFor(Customer::class)->index();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('review_likes');
    }
};
