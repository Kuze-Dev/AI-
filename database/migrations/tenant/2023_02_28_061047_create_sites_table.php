<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sites', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->softDeletes('deleted_at');
            $table->timestamps();

            // Indexes
            $table->index('name');
        });

        Schema::create('model_sites', function (Blueprint $table) {
            $table->unsignedBigInteger('model_sites_id');
            $table->unsignedBigInteger('site_id');
            $table->string('model_sites_type');

            $table->index('model_sites_id');
            $table->index('site_id');
            $table->index(['model_sites_id', 'model_sites_type']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('model_sites');
        Schema::dropIfExists('sites');
    }
};
