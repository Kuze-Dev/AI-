<?php

declare(strict_types=1);

use Domain\Auth\Enums\EmailVerificationType;
use Domain\Customer\Enums\RegisterStatus;
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

            $table->foreignIdFor(Tier::class)->index()->nullable();

            $table->string('cuid')->unique()->comment('customer unique ID');
            $table->string('email')->unique();
            $table->string('password')->nullable();
            $table->string('first_name')->index();
            $table->string('last_name')->index();
            $table->string('mobile')->nullable();
            $table->string('gender')->nullable();
            $table->string('status')->default(Status::ACTIVE->value)->index()->nullable();
            $table->string('register_status')->default(RegisterStatus::REGISTERED->value)->index();

            $table->rememberToken();
            $table->date('birth_date')->nullable();
            $table->string('email_verification_type', 100)->default(EmailVerificationType::LINK->value)->nullable();
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
