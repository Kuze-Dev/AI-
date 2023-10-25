<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('email_verification_one_time_passwords', function (Blueprint $table) {
            $table->id();

            $table->morphs('authenticatable', 'otp_auth_type_auth_id_index');
            $table->string('password');
            $table->timestamp('expired_at')->index();

            $table->timestamps();

            $table->unique(
                ['authenticatable_type', 'authenticatable_id'],
                'otp_auth_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('email_verification_one_time_passwords');
    }
};
