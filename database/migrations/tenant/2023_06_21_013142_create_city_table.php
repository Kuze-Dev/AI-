<?php

declare(strict_types=1);

use Domain\Address\Models\State;
use Domain\Address\Models\Region;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('cities', function (Blueprint $table) {
            $table->id();

            $table->foreignIdFor(State::class)->index()->nullable();
            $table->foreignIdFor(Region::class)->index()->nullable();
            $table->string('name')->index();

            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cities');
    }
};
