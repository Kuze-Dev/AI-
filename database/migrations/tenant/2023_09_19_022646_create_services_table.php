<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    /** Run the migrations. */
    public function up(): void
    {
        Schema::create('services', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(\Domain\Blueprint\Models\Blueprint::class)->index();
            $table->string('name')->unique();
            $table->text('description')->nullable();
            $table->integer('price');
            $table->string('billing_cycle')->nullable(); //monthly and yearly
            $table->string('recurring_payment')->nullable(); //daily, monthly and yearly
            $table->json('data')->nullable();
            $table->boolean('is_featured');
            $table->boolean('is_special_offer');
            $table->boolean('pay_upfront');
            $table->boolean('is_subscription');
            $table->boolean('status');
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('service_taxonomy_terms', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(\Domain\Service\Models\Service::class)->index();
            $table->foreignIdFor(\Domain\Taxonomy\Models\TaxonomyTerm::class)->index();
        });
    }

    /** Reverse the migrations. */
    public function down(): void
    {
        Schema::dropIfExists('services');
        Schema::dropIfExists('service_taxonomy_terms');
    }
};
