<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('google_drive_tokens', function (Blueprint $table) {
            $table->integer('token_created_at')->nullable()->after('expires_at');
        });
    }

    public function down(): void
    {
        Schema::table('google_drive_tokens', function (Blueprint $table) {
            $table->dropColumn('token_created_at');
        });
    }
};
