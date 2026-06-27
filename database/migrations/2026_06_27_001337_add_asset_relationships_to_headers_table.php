<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('headers', function (Blueprint $table) {
            $table->foreignId('asset_id')->nullable()->after('end_date')->constrained();
            $table->foreignId('destination_asset_id')->nullable()->after('asset_id')->constrained('assets');
        });
    }

    public function down(): void
    {
        Schema::table('headers', function (Blueprint $table) {
            $table->dropForeign(['destination_asset_id']);
            $table->dropForeign(['asset_id']);
            $table->dropColumn(['asset_id', 'destination_asset_id']);
        });
    }
};
