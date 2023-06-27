<?php

declare(strict_types=1);

use Domain\Address\Models\Country;
use Domain\Address\Models\Region;
use Domain\Address\Models\State;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('countries', function (Blueprint $table) {
            $table->id();

            $table->string('code')->index();
            $table->string('name')->index();
            $table->string('capital')->nullable();
            $table->string('state_or_region')->nullable();
            $table->string('timezone')->nullable();
            $table->string('language')->nullable();
            $table->boolean('active')->default(false);

            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('regions', function (Blueprint $table) {
            $table->id();

            $table->foreignIdFor(Country::class)->index();
            $table->string('name')->index();

            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('states', function (Blueprint $table) {
            $table->id();

            $table->foreignIdFor(Country::class)->index();
            $table->string('name')->index();

            $table->timestamps();
            $table->softDeletes();
        });

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
        Schema::dropIfExists('currencies');
    }
};
