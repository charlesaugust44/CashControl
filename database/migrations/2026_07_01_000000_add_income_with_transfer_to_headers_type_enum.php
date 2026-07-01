<?php

use App\Enums\EventType;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            return;
        }

        Schema::table('headers', function (Blueprint $table) {
            $table->enum('type', EventType::cases())->change();
        });
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            return;
        }

        $types = array_filter(EventType::cases(), fn($t) => $t !== 'income_with_transfer');

        Schema::table('headers', function (Blueprint $table) use ($types) {
            $table->enum('type', $types)->change();
        });
    }
};
