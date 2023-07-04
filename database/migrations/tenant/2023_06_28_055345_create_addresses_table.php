<?php

declare(strict_types=1);

use Domain\Address\Models\State;
use Domain\Customer\Models\Customer;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('addresses', function (Blueprint $table) {
            $table->id();

            $table->foreignIdFor(Customer::class)->index();
            $table->foreignIdFor(State::class)->index();

            $table->string('label_as');
            $table->text('address_line_1');
            $table->string('zip_code');
            $table->string('city');

            $table->boolean('is_default_shipping');
            $table->boolean('is_default_billing');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('addresses');
    }
};
