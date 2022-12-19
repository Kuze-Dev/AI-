<?php

declare(strict_types=1);
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Domain\Blueprint\Models\Blueprint as BlueprintModel;
use Domain\Collection\Models\Collection as CollectionModel;

return new class () extends Migration {
    /** Run the migrations. */
    public function up()
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
            // $table->dateTime('published_at')->nullable();
            $table->json('data');
            $table->bigInteger('order')->nullable();
            $table->timestamps();
        });
    }

    /** Reverse the migrations. */
    public function down()
    {
        Schema::dropIfExists('collections');
        Schema::dropIfExists('collection_entries');
    }
};
