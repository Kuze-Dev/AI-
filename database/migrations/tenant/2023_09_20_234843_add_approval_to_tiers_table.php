<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tiers', function (Blueprint $table) {
            $table->boolean('has_approval')
                ->default(1)
                ->after('description');
        });
    }
};
