<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sites', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique()->index();
            $table->softDeletes('deleted_at');
            $table->timestamps();
        });

        Schema::create('page_site', function (Blueprint $table) {
            $table->unsignedInteger('page_id');
            $table->unsignedInteger('site_id');
        });

        Schema::create('collection_site', function (Blueprint $table) {
            $table->unsignedInteger('collection_id');
            $table->unsignedInteger('site_id');
        });

        Schema::create('form_site', function (Blueprint $table) {
            $table->unsignedInteger('form_id');
            $table->unsignedInteger('site_id');
        });

        Schema::create('menu_site', function (Blueprint $table) {
            $table->unsignedInteger('menu_id');
            $table->unsignedInteger('site_id');
        });

        Schema::create('globals_site', function (Blueprint $table) {
            $table->unsignedInteger('globals_id');
            $table->unsignedInteger('site_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('sites');
    }
};
