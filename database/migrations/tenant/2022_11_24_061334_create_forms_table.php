<?php

declare(strict_types=1);

use Domain\Blueprint\Models\Blueprint as BlueprintModel;
use Domain\Form\Models\Form;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('forms', function (Blueprint $table) {
            $table->id();

            $table->foreignIdFor(BlueprintModel::class)->index();

            $table->string('name')->unique();
            $table->string('slug')->unique();

            $table->boolean('store_submission');

            $table->timestamps();
        });

        Schema::create('form_email_notifications', function (Blueprint $table) {
            $table->id();

            $table->foreignIdFor(Form::class)->index();

            $table->string('to');
            $table->string('cc')->nullable();
            $table->string('bcc')->nullable();
            $table->string('sender');
            $table->string('reply_to')->nullable();
            $table->string('subject');
            $table->longText('template');

            $table->timestamps();
        });

        Schema::create('form_submissions', function (Blueprint $table) {
            $table->id();

            $table->foreignIdFor(Form::class)->index();

            $table->json('data');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('form_submissions');
        Schema::dropIfExists('form_email_notifications');
        Schema::dropIfExists('forms');
    }
};
