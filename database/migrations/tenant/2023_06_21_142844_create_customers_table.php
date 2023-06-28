<?php

declare(strict_types=1);

use Domain\Address\Models\City;
use Domain\Address\Models\Country;
use Domain\Address\Models\Region;
use Domain\Address\Models\State;
use Domain\Customer\Enums\Status;
use Domain\Customer\Models\Customer;
use Domain\Tier\Models\Tier;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('tiers', function (Blueprint $table) {
            $table->id();

            $table->string('name')->unique();
            $table->text('description');

            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('customers', function (Blueprint $table) {
            $table->id();

            $table->foreignIdFor(Tier::class)->nullable()->index();

            $table->string('email')->unique();
            $table->string('password')->nullable();
            $table->string('first_name')->index();
            $table->string('last_name')->index();
            $table->string('mobile');
            $table->string('status')->default(Status::ACTIVE->value)->index();

            $table->date('birth_date');
            $table->timestamp('email_verified_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('addresses', function (Blueprint $table) {
            $table->id();

            $table->foreignIdFor(Customer::class)->index();
            $table->foreignIdFor(Country::class)->index();
            $table->foreignIdFor(State::class)->index()->nullable();
            $table->foreignIdFor(Region::class)->index()->nullable();
            $table->foreignIdFor(City::class)->index();

            $table->text('address_line_1');
            $table->text('address_line_2')->nullable();
            $table->string('zip_code');

            $table->boolean('is_default_shipping');
            $table->boolean('is_default_billing');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customers');
        Schema::dropIfExists('tiers');
    }
};
