<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->string('type')->nullable()->after('header_id');
            $table->string('name', 100)->nullable()->after('type');
            $table->foreignId('header_id')->nullable()->change();
        });

        $events = DB::table('events')
            ->whereNotNull('header_id')
            ->get();

        foreach ($events as $event) {
            $header = DB::table('headers')->where('id', $event->header_id)->first();
            if ($header) {
                DB::table('events')
                    ->where('id', $event->id)
                    ->update([
                        'type' => $header->type,
                        'name' => $header->name,
                    ]);
            }
        }
    }

    public function down(): void
    {
        DB::table('events')->whereNull('header_id')->delete();

        Schema::table('events', function (Blueprint $table) {
            $table->dropColumn(['type', 'name']);
            $table->foreignId('header_id')->nullable(false)->change();
        });
    }
};
