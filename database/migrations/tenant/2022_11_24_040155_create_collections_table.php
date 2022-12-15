<?php

declare (strict_types = 1);
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Domain\Blueprint\Models\Blueprint as BlueprintModel;
use Domain\Collection\Models\Collection as CollectionModel;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('collections', function (Blueprint $table) {
            $table->id();

            $table->foreignIdFor(BlueprintModel::class)->constrained();
            $table->string('name')->unique();
            $table->string('slug')->unique();
            $table->string('future_publish_date')->nullable();
            $table->string('past_publish_date')->nullable();
            $table->boolean('is_sortable')->default(false);
            $table->json('data')->nullable();

            $table->timestamps();
        });

        Schema::create('collection_entries', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('order')->nullable();
            $table->foreignIdFor(CollectionModel::class)->constrained();
            $table->json('data');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('collections');
        Schema::dropIfExists('collection_entries');
    }
};
