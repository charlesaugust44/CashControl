<?php

namespace Tests\Feature\Unity;

use App\Models\User;
use App\Models\Unity;
use App\Models\Asset;
use App\Models\Header;
use App\Models\Event;
use App\Models\Entry;
use App\Enums\EventType;
use App\Enums\EventRule;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class UnityScopingTest extends TestCase
{
    use RefreshDatabase;

    private $user1;
    private $user2;
    private $unity1;
    private $unity2;

    protected function setUp(): void
    {
        parent::setUp();

        // Create two users
        $this->user1 = User::create([
            'name' => 'User 1',
            'email' => 'user1@example.com',
            'password' => Hash::make('password123'),
            'role' => 'common',
            'approved_at' => now(),
        ]);

        $this->user2 = User::create([
            'name' => 'User 2',
            'email' => 'user2@example.com',
            'password' => Hash::make('password123'),
            'role' => 'common',
            'approved_at' => now(),
        ]);

        // Create two unities
        $this->unity1 = Unity::create([
            'name' => 'Unity 1',
            'description' => 'First Unity',
        ]);

        $this->unity2 = Unity::create([
            'name' => 'Unity 2',
            'description' => 'Second Unity',
        ]);

        // Assign users to unities
        $this->user1->unities()->attach($this->unity1->id);
        $this->user2->unities()->attach($this->unity2->id);
    }

    public function test_user_can_only_see_their_unity_assets()
    {
        // Create assets for both unities
        $asset1 = Asset::create([
            'name' => 'Asset 1',
            'balance' => 1000.00,
            'unity_id' => $this->unity1->id,
        ]);

        $asset2 = Asset::create([
            'name' => 'Asset 2',
            'balance' => 2000.00,
            'unity_id' => $this->unity2->id,
        ]);

        // User 1 should only see their asset
        $response = $this->actingAs($this->user1)->get('/assets');
        $response->assertStatus(200);
        $response->assertSee('Asset 1');
        $response->assertDontSee('Asset 2');

        // User 2 should only see their asset
        $response = $this->actingAs($this->user2)->get('/assets');
        $response->assertStatus(200);
        $response->assertSee('Asset 2');
        $response->assertDontSee('Asset 1');
    }

    public function test_user_cannot_access_asset_from_another_unity()
    {
        $asset1 = Asset::create([
            'name' => 'Asset 1',
            'balance' => 1000.00,
            'unity_id' => $this->unity1->id,
        ]);

        $asset2 = Asset::create([
            'name' => 'Asset 2',
            'balance' => 2000.00,
            'unity_id' => $this->unity2->id,
        ]);

        // User 2 should not be able to access User 1's asset
        $response = $this->actingAs($this->user2)->get("/assets/{$asset1->id}");
        $response->assertStatus(404);
    }

    public function test_user_can_only_see_their_unity_templates()
    {
        $asset1 = Asset::create([
            'name' => 'Asset 1',
            'balance' => 1000.00,
            'unity_id' => $this->unity1->id,
        ]);

        $asset2 = Asset::create([
            'name' => 'Asset 2',
            'balance' => 2000.00,
            'unity_id' => $this->unity2->id,
        ]);

        // Create headers for both unities
        $header1 = Header::create([
            'name' => 'Header 1',
            'type' => EventType::Expense,
            'rule' => EventRule::Fixed,
            'default_amount' => 100.00,
            'start_date' => now()->startOfMonth(),
            'asset_id' => $asset1->id,
            'unity_id' => $this->unity1->id,
        ]);

        $header2 = Header::create([
            'name' => 'Header 2',
            'type' => EventType::Expense,
            'rule' => EventRule::Fixed,
            'default_amount' => 200.00,
            'start_date' => now()->startOfMonth(),
            'asset_id' => $asset2->id,
            'unity_id' => $this->unity2->id,
        ]);

        // User 1 should only see their header
        $response = $this->actingAs($this->user1)->get('/templates');
        $response->assertStatus(200);
        $response->assertSee('Header 1');
        $response->assertDontSee('Header 2');

        // User 2 should only see their header
        $response = $this->actingAs($this->user2)->get('/templates');
        $response->assertStatus(200);
        $response->assertSee('Header 2');
        $response->assertDontSee('Header 1');
    }

    public function test_user_can_only_see_their_unity_events()
    {
        $asset1 = Asset::create([
            'name' => 'Asset 1',
            'balance' => 1000.00,
            'unity_id' => $this->unity1->id,
        ]);

        $asset2 = Asset::create([
            'name' => 'Asset 2',
            'balance' => 2000.00,
            'unity_id' => $this->unity2->id,
        ]);

        $header1 = Header::create([
            'name' => 'Header 1',
            'type' => EventType::Expense,
            'rule' => EventRule::Fixed,
            'default_amount' => 100.00,
            'start_date' => now()->startOfMonth(),
            'asset_id' => $asset1->id,
            'unity_id' => $this->unity1->id,
        ]);

        $header2 = Header::create([
            'name' => 'Header 2',
            'type' => EventType::Expense,
            'rule' => EventRule::Fixed,
            'default_amount' => 200.00,
            'start_date' => now()->startOfMonth(),
            'asset_id' => $asset2->id,
            'unity_id' => $this->unity2->id,
        ]);

        // Create events for both unities
        $event1 = Event::create([
            'header_id' => $header1->id,
            'type' => EventType::Expense,
            'name' => 'Event 1',
            'date' => now(),
            'consolidated' => false,
            'transfer_consolidated' => false,
            'unity_id' => $this->unity1->id,
        ]);

        $event2 = Event::create([
            'header_id' => $header2->id,
            'type' => EventType::Expense,
            'name' => 'Event 2',
            'date' => now(),
            'consolidated' => false,
            'transfer_consolidated' => false,
            'unity_id' => $this->unity2->id,
        ]);

        // User 1 should only see their event
        $response = $this->actingAs($this->user1)->get('/entries');
        $response->assertStatus(200);
        $response->assertSee('Event 1');
        $response->assertDontSee('Event 2');

        // User 2 should only see their event
        $response = $this->actingAs($this->user2)->get('/entries');
        $response->assertStatus(200);
        $response->assertSee('Event 2');
        $response->assertDontSee('Event 1');
    }

    public function test_dashboard_shows_only_unity_data()
    {
        $asset1 = Asset::create([
            'name' => 'Asset 1',
            'balance' => 1000.00,
            'unity_id' => $this->unity1->id,
        ]);

        $asset2 = Asset::create([
            'name' => 'Asset 2',
            'balance' => 2000.00,
            'unity_id' => $this->unity2->id,
        ]);

        // User 1 should see only their asset balance
        $response = $this->actingAs($this->user1)->get('/');
        $response->assertStatus(200);
        $response->assertSee('Asset 1');
        $response->assertDontSee('Asset 2');

        // User 2 should see only their asset balance
        $response = $this->actingAs($this->user2)->get('/');
        $response->assertStatus(200);
        $response->assertSee('Asset 2');
        $response->assertDontSee('Asset 1');
    }
}
