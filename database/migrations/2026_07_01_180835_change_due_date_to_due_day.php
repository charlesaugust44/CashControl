<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('headers', function (Blueprint $table) {
            $table->dropColumn('due_date');
            $table->tinyInteger('due_day')->nullable()->after('end_date');
        });

        Schema::table('events', function (Blueprint $table) {
            $table->dropColumn('due_date');
            $table->tinyInteger('due_day')->nullable()->after('date');
        });
    }

    public function down(): void
    {
        Schema::table('headers', function (Blueprint $table) {
            $table->dropColumn('due_day');
            $table->date('due_date')->nullable()->after('end_date');
        });

        Schema::table('events', function (Blueprint $table) {
            $table->dropColumn('due_day');
            $table->date('due_date')->nullable()->after('date');
        });
    }
};
