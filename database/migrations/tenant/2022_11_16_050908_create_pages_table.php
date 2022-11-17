<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Domain\Blueprint\Models\Blueprint as BlueprintModel;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('pages', function (Blueprint $table) {
            $table->id();

            $table->foreignIdFor(BlueprintModel::class)->constrained();

            $table->string('name')->unique();

            $table->string('past_behavior')
                ->nullable()
                ->comment('php enum');

            $table->string('future_behavior')
                ->nullable()
                ->comment('php enum');

            $table->json('data')->nullable();

            $table->timestamp('published_at')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pages');
    }
};
