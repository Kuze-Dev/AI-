<?php

declare(strict_types=1);
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Domain\Blueprint\Models\Blueprint as BlueprintModel;
use Domain\Collection\Models\Collection as CollectionModel;

return new class () extends Migration {
    /** Run the migrations. */
    public function up(): void
    {
        Schema::create('collections', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(BlueprintModel::class)->constrained();
            $table->string('name')->unique();
            $table->string('slug')->unique();
            $table->string('future_publish_date_behavior')->nullable();
            $table->string('past_publish_date_behavior')->nullable();
            $table->boolean('is_sortable')->default(false);
            $table->timestamps();
        });

        Schema::create('collection_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(CollectionModel::class)->constrained();
            $table->string('title')->unique();
            $table->string('slug')->unique();
            $table->dateTime('published_at')->nullable();
            $table->json('data');
            $table->bigInteger('order')->nullable();
            $table->timestamps();
        });

        Schema::create('collection_taxonomies', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('collection_id')->unsigned();
            $table->unsignedBigInteger('taxonomy_id')->unsigned();
            $table->foreign('collection_id')
                ->references('id')
                ->on('collections')
                ->onDelete('cascade');
            $table->foreign('taxonomy_id')
                ->references('id')
                ->on('taxonomies')
                ->onDelete('cascade');
        });

        Schema::create('collection_entries_taxonomy_terms', function (Blueprint $table) {
            $table->id();
            $table->unsignedBiginteger('taxonomy_terms_id')->unsigned();
            $table->unsignedBiginteger('collection_entries_id')->unsigned();
            $table->foreign('taxonomy_terms_id')
                ->references('id')
                ->on('taxonomy_terms')
                ->onDelete('cascade');
            $table->foreign('collection_entries_id')
                ->references('id')
                ->on('collection_entries')
                ->onDelete('cascade');
        });
    }

    /** Reverse the migrations. */
    public function down(): void
    {
        Schema::dropIfExists('collections');
        Schema::dropIfExists('collection_entries');
        Schema::dropIfExists('collection_entries_taxonomy_terms');
    }
};
