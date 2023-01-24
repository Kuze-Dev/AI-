<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('menus', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('slug');
            $table->timestamps();
        });

        Schema::create('nodes', function (Blueprint $table) {
            $table->id();
            $table->string('label');
            $table->bigInteger('menu_id')->unsigned()->index();
            $table->bigInteger('parent_id')->unsigned()->nullable()->index();
            $table->string('url')->nullable();
            $table->string('target');
            $table->integer('order')->unsigned();
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
        Schema::dropIfExists('nodes');
        Schema::dropIfExists('menus');
    }
};
