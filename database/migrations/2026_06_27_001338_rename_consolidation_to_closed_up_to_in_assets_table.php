<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('assets', function (Blueprint $table) {
            $table->date('closed_up_to')->nullable()->after('balance');
        });

        DB::table('assets')->update(['closed_up_to' => null]);

        Schema::table('assets', function (Blueprint $table) {
            $table->dropColumn('consolidation');
        });
    }

    public function down(): void
    {
        Schema::table('assets', function (Blueprint $table) {
            $table->date('consolidation')->default('1900-01-01')->after('balance');
        });

        Schema::table('assets', function (Blueprint $table) {
            $table->dropColumn('closed_up_to');
        });
    }
};
