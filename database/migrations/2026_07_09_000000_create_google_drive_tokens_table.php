<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('google_drive_tokens', function (Blueprint $table) {
            $table->id();
            $table->text('access_token');
            $table->text('refresh_token');
            $table->integer('expires_at')->nullable();
            $table->foreignId('connected_by')->constrained('users')->cascadeOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('google_drive_tokens');
    }
};
