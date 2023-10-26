<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::connection(config('activitylog.database_connection'))->table(config('activitylog.table_name'), function (Blueprint $table) {
            // https://github.com/spatie/laravel-activitylog/issues/835#issuecomment-760948995
            $table->string('subject_type')->nullable()->change();
            $table->string('subject_id')->nullable()->change();

            $table->index(['subject_type', 'subject_id']);
        });
    }
};
