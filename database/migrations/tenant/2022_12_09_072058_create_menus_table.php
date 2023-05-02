<?php

declare(strict_types=1);

use Domain\Menu\Models\Menu;
use Domain\Menu\Models\Node;
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
        Schema::create('menus', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('slug');
            $table->timestamps();
        });

        Schema::create('nodes', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Menu::class)->index();
            $table->foreignIdFor(Node::class, 'parent_id')->nullable()->index();
            $table->string('label');
            $table->string('target');
            $table->string('url')->nullable();
            $table->unsignedInteger('order');
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
