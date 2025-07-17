<?php

declare(strict_types=1);

use Domain\Admin\Models\Admin;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('tenant_api_keys', function (Blueprint $table) {
            $table->id();
            $table->text('app_name')->comment('Name of the application using the API key');
            $table->string('api_key')->unique()->comment('Unique API key');
            $table->string('secret_key')->unique()->comment('API Secret key');
            $table->json('abilities')->nullable()->comment('Abilities granted by the API key');
            $table->foreignIdFor(Admin::class)
                ->constrained()
                ->cascadeOnDelete()
                ->comment('Admin who created the API key');
            $table->timestamp('last_used_at')->nullable()->comment('Expiration date of the API key');
            $table->timestamp('expires_at')->nullable()->comment('Expiration date of the API key');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tenant_api_keys');
    }
};
