<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

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
            $table->bigInteger('taxonomy_id')->unsigned()->index();
            $table->bigInteger('parent_id')->unsigned()->nullable()->index();
            $table->string('name')->unique();
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->integer('order')->unsigned();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('taxonomy_terms');
        Schema::dropIfExists('taxonomies');
    }
};
