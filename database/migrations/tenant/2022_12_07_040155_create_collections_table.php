<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Domain\Blueprint\Models\Blueprint as BlueprintModel;
use Domain\Content\Models\Content;
use Domain\Content\Models\ContentEntry;
use Domain\Taxonomy\Models\Taxonomy;
use Domain\Taxonomy\Models\TaxonomyTerm;

return new class () extends Migration {
    /** Run the migrations. */
    public function up(): void
    {
        Schema::create('contents', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(BlueprintModel::class)->index();
            $table->string('name')->unique();
            $table->string('slug')->unique();
            $table->string('prefix')->unique();
            $table->string('future_publish_date_behavior')->nullable();
            $table->string('past_publish_date_behavior')->nullable();
            $table->boolean('is_sortable')->default(false);
            $table->timestamps();
        });

        Schema::create('content_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Content::class)->index();
            $table->string('title');
            $table->string('slug')->index();
            $table->dateTime('published_at')->nullable();
            $table->json('data');
            $table->unsignedInteger('order')->nullable();
            $table->timestamps();

            $table->unique(['content_id', 'title']);
        });

        Schema::create('content_taxonomy', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Content::class)->index();
            $table->foreignIdFor(Taxonomy::class)->index();
        });

        Schema::create('content_entry_taxonomy_term', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(ContentEntry::class)->index();
            $table->foreignIdFor(TaxonomyTerm::class)->index();
        });
    }

    /** Reverse the migrations. */
    public function down(): void
    {
        Schema::dropIfExists('content_entry_taxonomy_term');
        Schema::dropIfExists('content_taxonomy');
        Schema::dropIfExists('content_entries');
        Schema::dropIfExists('contents');
    }
};
