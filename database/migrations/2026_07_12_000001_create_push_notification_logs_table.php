<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('push_notification_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('event_key', 200);
            $table->string('notification_type', 50);
            $table->timestamps();

            $table->unique(['user_id', 'event_key', 'notification_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('push_notification_logs');
    }
};
