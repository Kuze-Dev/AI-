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
            $table->tinyInteger('display_publish_dates')->default(0);
            $table->string('future_publish_date')->nullable();
            $table->string('past_publish_date')->nullable();
            $table->tinyInteger('isSortable')->default(0);
            $table->enum('order_direction', ['asc', 'desc'])->default('asc');
            $table->json('data')->nullable();
            
            $table->timestamps();
        });

        Schema::create('collection_entries', function (Blueprint $table) {
            $table->id();
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
