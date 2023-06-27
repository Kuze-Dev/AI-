<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    /** Run the migrations. */
    public function up(): void
    {
        Schema::create('currencies', function (Blueprint $table) {
            $table->id();

            $table->string('code')->nullable();
            $table->string('name')->nullable();
            $table->boolean('enabled')->default(false);
            $table->decimal('exchange_rate', 8, 2)->nullable();
            $table->boolean('default')->default(false);
            
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /** Reverse the migrations. */
    public function down(): void
    {
        Schema::dropIfExists('currencies');
    }
};
