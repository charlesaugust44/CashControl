<?php

use App\Models\Unity;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('assets', function (Blueprint $table) {
            $table->foreignId('unity_id')->nullable()->after('id')->constrained('unities')->nullOnDelete();
            $table->foreignId('created_by')->nullable()->after('updated_at')->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->after('created_by')->constrained('users')->nullOnDelete();
        });

        Schema::table('headers', function (Blueprint $table) {
            $table->foreignId('unity_id')->nullable()->after('id')->constrained('unities')->nullOnDelete();
            $table->foreignId('created_by')->nullable()->after('updated_at')->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->after('created_by')->constrained('users')->nullOnDelete();
        });

        Schema::table('events', function (Blueprint $table) {
            $table->foreignId('unity_id')->nullable()->after('id')->constrained('unities')->nullOnDelete();
            $table->foreignId('created_by')->nullable()->after('updated_at')->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->after('created_by')->constrained('users')->nullOnDelete();
        });

        Schema::table('entries', function (Blueprint $table) {
            $table->foreignId('created_by')->nullable()->after('updated_at')->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->after('created_by')->constrained('users')->nullOnDelete();
        });

        $defaultUnity = Unity::create([
            'name' => 'Default',
            'description' => 'Default unity for existing data',
        ]);

        DB::table('assets')->whereNull('unity_id')->update(['unity_id' => $defaultUnity->id]);
        DB::table('headers')->whereNull('unity_id')->update(['unity_id' => $defaultUnity->id]);
        DB::table('events')->whereNull('unity_id')->update(['unity_id' => $defaultUnity->id]);
    }

    public function down(): void
    {
        Schema::table('entries', function (Blueprint $table) {
            $table->dropForeign(['updated_by']);
            $table->dropForeign(['created_by']);
            $table->dropColumn(['created_by', 'updated_by']);
        });

        Schema::table('events', function (Blueprint $table) {
            $table->dropForeign(['unity_id']);
            $table->dropForeign(['created_by']);
            $table->dropForeign(['updated_by']);
            $table->dropColumn(['unity_id', 'created_by', 'updated_by']);
        });

        Schema::table('headers', function (Blueprint $table) {
            $table->dropForeign(['unity_id']);
            $table->dropForeign(['created_by']);
            $table->dropForeign(['updated_by']);
            $table->dropColumn(['unity_id', 'created_by', 'updated_by']);
        });

        Schema::table('assets', function (Blueprint $table) {
            $table->dropForeign(['unity_id']);
            $table->dropForeign(['created_by']);
            $table->dropForeign(['updated_by']);
            $table->dropColumn(['unity_id', 'created_by', 'updated_by']);
        });
    }
};
