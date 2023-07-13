<?php

declare(strict_types=1);

use Domain\Address\Models\Country;
use Domain\Address\Models\State;
use Domain\Taxation\Models\TaxZone;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    /** Run the migrations. */
    public function up(): void
    {
        Schema::create('tax_zones', function (Blueprint $table) {
            $table->id();

            $table->string('name');
            $table->string('price_display');
            $table->boolean('is_active')->index();
            $table->boolean('is_default')->index();
            $table->string('type');
            $table->decimal('percentage', 7, 3);

            $table->timestamps();
        });

        Schema::create('tax_zone_country', function (Blueprint $table) {
            $table->foreignIdFor(TaxZone::class)->index();
            $table->foreignIdFor(Country::class)->index();
        });

        Schema::create('tax_zone_state', function (Blueprint $table) {
            $table->foreignIdFor(TaxZone::class)->index();
            $table->foreignIdFor(State::class)->index();
        });
    }

    /** Reverse the migrations. */
    public function down(): void
    {
        Schema::dropIfExists('tax_zones');
        Schema::dropIfExists('tax_zone_country');
        Schema::dropIfExists('tax_zone_state');
    }
};
