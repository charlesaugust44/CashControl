<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('unity_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('unity_id')->constrained('unities')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['unity_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('unity_user');
    }
};
