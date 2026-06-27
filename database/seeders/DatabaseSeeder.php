<?php

namespace Database\Seeders;

use App\Enums\EventRule;
use App\Enums\EventType;
use App\Models\Asset;
use App\Models\Header;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        User::firstOrCreate([
            'email' => 'test@example.com',
        ], [
            'name' => 'Test User',
            'password' => Hash::make('password'),
        ]);

        $checking = Asset::factory()->create(['name' => 'Checking Account', 'balance' => 2500.00]);
        $savings = Asset::factory()->create(['name' => 'Savings Account', 'balance' => 8000.00]);
        $cash = Asset::factory()->create(['name' => 'Cash', 'balance' => 150.00]);
        $creditCard = Asset::factory()->create(['name' => 'Credit Card', 'balance' => -450.00]);
        $investment = Asset::factory()->create(['name' => 'Investment Account', 'balance' => 15000.00]);

        Header::factory()->withEventEntries($checking)->create([
            'name' => 'Monthly Salary',
            'description' => 'Regular monthly income from employment',
            'type' => EventType::Income,
            'rule' => EventRule::Fixed,
            'default_amount' => 4500.00,
            'start_date' => Carbon::now()->subYear(),
            'end_date' => null,
            'asset_id' => $checking->id,
        ]);

        Header::factory()->withEventEntries($checking)->create([
            'name' => 'Freelance Income',
            'description' => 'Side project consulting work',
            'type' => EventType::Income,
            'rule' => EventRule::MaxLastFiveMonths,
            'default_amount' => 800.00,
            'start_date' => Carbon::now()->subMonths(8),
            'end_date' => null,
            'asset_id' => $checking->id,
        ]);

        Header::factory()->withEventEntries($checking)->create([
            'name' => 'Rent Payment',
            'description' => 'Monthly apartment rent',
            'type' => EventType::Expense,
            'rule' => EventRule::Fixed,
            'default_amount' => 1200.00,
            'start_date' => Carbon::now()->subYear(),
            'end_date' => null,
            'asset_id' => $checking->id,
        ]);

        Header::factory()->withEventEntries($checking)->create([
            'name' => 'Electricity Bill',
            'description' => 'Monthly electricity and utilities',
            'type' => EventType::Expense,
            'rule' => EventRule::MeanLastFiveMonths,
            'default_amount' => 120.00,
            'start_date' => Carbon::now()->subMonths(10),
            'end_date' => null,
            'asset_id' => $checking->id,
        ]);

        Header::factory()->withEventEntries($checking)->create([
            'name' => 'Internet Service',
            'description' => 'Home internet subscription',
            'type' => EventType::Expense,
            'rule' => EventRule::Fixed,
            'default_amount' => 79.99,
            'start_date' => Carbon::now()->subYear(),
            'end_date' => null,
            'asset_id' => $checking->id,
        ]);

        Header::factory()->withEventEntries($checking)->create([
            'name' => 'Car Insurance',
            'description' => 'Monthly car insurance premium',
            'type' => EventType::Expense,
            'rule' => EventRule::Fixed,
            'default_amount' => 145.00,
            'start_date' => Carbon::now()->subMonths(9),
            'end_date' => null,
            'asset_id' => $checking->id,
        ]);

        Header::factory()->withEventEntries($creditCard)->create([
            'name' => 'Gym Membership',
            'description' => 'Monthly fitness center membership',
            'type' => EventType::Expense,
            'rule' => EventRule::Fixed,
            'default_amount' => 49.99,
            'start_date' => Carbon::now()->subMonths(6),
            'end_date' => null,
            'asset_id' => $creditCard->id,
        ]);

        Header::factory()->withEventEntries($creditCard)->create([
            'name' => 'Groceries',
            'description' => 'Weekly grocery shopping',
            'type' => EventType::Expense,
            'rule' => EventRule::MeanLastFiveMonths,
            'default_amount' => 400.00,
            'start_date' => Carbon::now()->subMonths(11),
            'end_date' => null,
            'asset_id' => $creditCard->id,
        ]);

        Header::factory()->withEventEntries($creditCard)->create([
            'name' => 'Dining Out',
            'description' => 'Restaurants and takeout',
            'type' => EventType::Expense,
            'rule' => EventRule::MaxLastFiveMonths,
            'default_amount' => 200.00,
            'start_date' => Carbon::now()->subMonths(7),
            'end_date' => null,
            'asset_id' => $creditCard->id,
        ]);

        Header::factory()->withEventEntries($creditCard)->create([
            'name' => 'Gas Station',
            'description' => 'Fuel for vehicle',
            'type' => EventType::Expense,
            'rule' => EventRule::MeanLastFiveMonths,
            'default_amount' => 150.00,
            'start_date' => Carbon::now()->subMonths(10),
            'end_date' => null,
            'asset_id' => $creditCard->id,
        ]);

        Header::factory()->withEventEntries($cash)->create([
            'name' => 'Entertainment',
            'description' => 'Movies, streaming, and hobbies',
            'type' => EventType::Expense,
            'rule' => EventRule::MaxLastFiveMonths,
            'default_amount' => 100.00,
            'start_date' => Carbon::now()->subMonths(8),
            'end_date' => null,
            'asset_id' => $cash->id,
        ]);

        Header::factory()->withEventEntries($checking)->create([
            'name' => 'Transfer to Savings',
            'description' => 'Monthly savings contribution',
            'type' => EventType::Transfer,
            'rule' => EventRule::Fixed,
            'default_amount' => 500.00,
            'start_date' => Carbon::now()->subYear(),
            'end_date' => null,
            'asset_id' => $checking->id,
            'destination_asset_id' => $savings->id,
        ]);

        Header::factory()->withEventEntries($checking)->create([
            'name' => 'ATM Withdrawal',
            'description' => 'Cash withdrawal from checking',
            'type' => EventType::Transfer,
            'rule' => EventRule::MeanLastFiveMonths,
            'default_amount' => 200.00,
            'start_date' => Carbon::now()->subMonths(9),
            'end_date' => null,
            'asset_id' => $checking->id,
            'destination_asset_id' => $cash->id,
        ]);
    }
}
