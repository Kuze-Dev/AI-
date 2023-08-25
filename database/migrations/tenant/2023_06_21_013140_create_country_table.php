<?php

declare(strict_types=1);

use Domain\Address\Models\Country;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('countries', function (Blueprint $table) {
            $table->id();

            $table->string('code')->unique();
            $table->string('name')->unique();
            $table->string('capital')->nullable();
            $table->string('timezone')->nullable();
            $table->boolean('active')->default(false);

            $table->timestamps();
        });

        Schema::create('states', function (Blueprint $table) {
            $table->id();

            $table->foreignIdFor(Country::class)->index();
            $table->string('code')->index();
            $table->string('name')->index();

            $table->timestamps();
        });

    }

    public function down(): void
    {
        Schema::dropIfExists('states');
        Schema::dropIfExists('countries');
    }
};
