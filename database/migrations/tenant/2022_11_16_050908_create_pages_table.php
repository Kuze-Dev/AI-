<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Domain\Blueprint\Models\Blueprint as BlueprintModel;
use Domain\Page\Models\Page;
use Domain\Page\Models\Slice;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('pages', function (Blueprint $table) {
            $table->id();

            $table->string('name')->unique();
            $table->string('slug')->unique();

            $table->timestamps();
        });

        Schema::create('slices', function (Blueprint $table) {
            $table->id();

            $table->foreignIdFor(BlueprintModel::class)->constrained();

            $table->string('name')->unique();
            $table->string('component')->unique();

            $table->timestamps();
        });

        Schema::create('slice_contents', function (Blueprint $table) {
            $table->id();

            $table->foreignIdFor(Slice::class)->constrained();
            $table->foreignIdFor(Page::class)->constrained()->cascadeOnDelete();

            $table->json('data')->nullable();
            $table->unsignedInteger('order');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('slice_contents');
        Schema::dropIfExists('slices');
        Schema::dropIfExists('pages');
    }
};
