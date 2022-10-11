<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('two_factor_authentications', function (Blueprint $table) {
            $table->id();
            $table->morphs('authenticatable', '2fa_auth_type_auth_id_index');
            $table->timestamp('enabled_at')->nullable();
            $table->string('secret')->nullable();
            $table->timestamps();
        });

        Schema::create('recovery_codes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('two_factor_authentication_id')->constrained()->cascadeOnDelete();
            $table->string('code');
            $table->timestamp('used_at')->nullable();
            $table->timestamps();
        });

        Schema::create('safe_devices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('two_factor_authentication_id')->constrained()->cascadeOnDelete();
            $table->string('ip');
            $table->text('user_agent');
            $table->string('remember_token', 100);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('two_factor_authentications');
        Schema::dropIfExists('recovery_codes');
        Schema::dropIfExists('safe_devices');
    }
};
