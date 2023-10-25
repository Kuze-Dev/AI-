<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /** Run the migrations. */
    public function up(): void
    {
        Schema::table('form_email_notifications', function (Blueprint $table) {
            $table->dropColumn('sender');
        });

        Schema::table('form_email_notifications', function (Blueprint $table) {
            $table->string('sender_name')->nullable()->after('bcc');
        });
    }

    /** Reverse the migrations. */
    public function down(): void
    {
        Schema::table('form_email_notifications', function (Blueprint $table) {
            $table->string('sender')->after('bcc');
            $table->dropColumn('sender_name');
        });
    }
};
