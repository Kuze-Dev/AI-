<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Domain\Taxonomy\Models\Taxonomy as TaxonomyModel;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('taxonomies', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('slug')->unique();
            $table->timestamps();
        });

        Schema::create('taxonomy_terms', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(TaxonomyModel::class)->constrained();
            $table->string('name')->unique();
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->unsignedInteger('order')->default(1);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('taxonomy_terms');
        Schema::dropIfExists('taxonomies');
    }
};
