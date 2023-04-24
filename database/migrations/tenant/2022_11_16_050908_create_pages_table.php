<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Domain\Blueprint\Models\Blueprint as BlueprintModel;
use Domain\Page\Models\Page;
use Domain\Page\Models\Block;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('pages', function (Blueprint $table) {
            $table->id();

            $table->string('name')->unique();
            $table->string('slug')->unique();
            $table->string('route_url');

            $table->timestamps();
        });

        Schema::create('blocks', function (Blueprint $table) {
            $table->id();

            $table->foreignIdFor(BlueprintModel::class)->index();

            $table->string('name')->unique();
            $table->string('component');
            $table->boolean('is_fixed_content')->default(false);
            $table->json('data')->nullable();

            $table->timestamps();
        });

        Schema::create('block_contents', function (Blueprint $table) {
            $table->id();

            $table->foreignIdFor(Block::class)->index();
            $table->foreignIdFor(Page::class)->index();

            $table->json('data')->nullable();
            $table->unsignedInteger('order');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('block_contents');
        Schema::dropIfExists('blocks');
        Schema::dropIfExists('pages');
    }
};
