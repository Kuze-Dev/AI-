<?php

declare(strict_types=1);

use Domain\Blueprint\Models\Blueprint as BlueprintModel;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('blueprint_data', function (Blueprint $table) {
            $table->id();

            $table->foreignIdFor(BlueprintModel::class)->index();
            $table->morphs('model');

            $table->string('state_path')->index();
            $table->string('value')->nullable();
            $table->string('type')->index();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('blueprint_data');
    }
};
