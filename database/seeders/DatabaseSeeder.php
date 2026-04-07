<?php

namespace Database\Seeders;

use App\Models\Asset;
use App\Models\Header;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        $assets = Asset::factory()->count(5)->create();

        foreach ($assets as $asset) {
            Header::factory()
                ->count(15)
                ->withEventEntries($asset)
                ->create();
        }

    }
}
