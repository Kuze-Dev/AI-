<?php

declare(strict_types=1);

use Domain\Menu\Enums\NodeType;
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
        Schema::table('nodes', function (Blueprint $table) {
            $table->after('parent_id', function (Blueprint $table) {
                $table->nullableMorphs('model');
            });

            $table->after('target', function (Blueprint $table) {
                $table->string('type')->default(NodeType::URL->value);
            });
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('nodes', function (Blueprint $table) {
            $table->dropMorphs('model');
            $table->dropColumn('type');
        });
    }
};
