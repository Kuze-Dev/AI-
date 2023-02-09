<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Domain\Blueprint\Models\Blueprint as BlueprintModel;
use Domain\Collection\Models\Collection;
use Domain\Collection\Models\CollectionEntry;
use Domain\Taxonomy\Models\Taxonomy;
use Domain\Taxonomy\Models\TaxonomyTerm;

return new class () extends Migration {
    /** Run the migrations. */
    public function up(): void
    {
        Schema::create('collections', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(BlueprintModel::class)->index();
            $table->string('name')->unique();
            $table->string('slug')->unique();
            $table->string('future_publish_date_behavior')->nullable();
            $table->string('past_publish_date_behavior')->nullable();
            $table->boolean('is_sortable')->default(false);
            $table->string('route_url')->unique();
            $table->timestamps();
        });

        Schema::create('collection_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Collection::class)->index();
            $table->string('title');
            $table->string('slug')->index();
            $table->dateTime('published_at')->nullable();
            $table->json('data');
            $table->unsignedInteger('order')->nullable();
            $table->timestamps();

            $table->unique(['collection_id', 'title']);
        });

        Schema::create('collection_taxonomy', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Collection::class)->index();
            $table->foreignIdFor(Taxonomy::class)->index();
        });

        Schema::create('collection_entry_taxonomy_term', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(CollectionEntry::class)->index();
            $table->foreignIdFor(TaxonomyTerm::class)->index();
        });
    }

    /** Reverse the migrations. */
    public function down(): void
    {
        Schema::dropIfExists('collection_entry_taxonomy_term');
        Schema::dropIfExists('collection_taxonomy');
        Schema::dropIfExists('collection_entries');
        Schema::dropIfExists('collections');
    }
};
