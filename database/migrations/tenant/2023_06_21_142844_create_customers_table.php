<?php

declare(strict_types=1);

use Domain\Customer\Enums\Status;
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

            $table->string('cuid')->unique()->comment('customer unique ID');
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

    }

    public function down(): void
    {
        Schema::dropIfExists('customers');
        Schema::dropIfExists('tiers');
    }
};
