<?php

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
        Schema::table('slices', function (Blueprint $table) {
            $table->boolean('is_fixed_content');
            $table->json('data')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('slices', function (Blueprint $table) {
            $table->dropColumn('is_fixed_content');
            $table->dropColumn('data');
        });
    }
};
