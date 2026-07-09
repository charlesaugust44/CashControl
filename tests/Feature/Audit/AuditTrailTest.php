<?php

namespace Tests\Feature\Audit;

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

class AuditTrailTest extends TestCase
{
    use RefreshDatabase;

    private $user;
    private $unity;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => Hash::make('password123'),
            'role' => 'common',
            'approved_at' => now(),
        ]);

        $this->unity = Unity::create([
            'name' => 'Test Unity',
            'description' => 'Test Description',
        ]);

        $this->user->unities()->attach($this->unity->id);
    }

    public function test_asset_creation_sets_audit_fields()
    {
        $response = $this->actingAs($this->user)->post('/assets', [
            'name' => 'Test Asset',
            'balance' => 1000.00,
        ]);

        $response->assertRedirect('/assets');

        $asset = Asset::where('name', 'Test Asset')->first();
        $this->assertNotNull($asset);
        $this->assertEquals($this->user->id, $asset->created_by);
        $this->assertEquals($this->user->id, $asset->updated_by);
    }

    public function test_asset_update_sets_updated_by()
    {
        $asset = Asset::create([
            'name' => 'Test Asset',
            'balance' => 1000.00,
            'unity_id' => $this->unity->id,
            'created_by' => $this->user->id,
            'updated_by' => $this->user->id,
        ]);

        $response = $this->actingAs($this->user)->put("/assets/{$asset->id}", [
            'name' => 'Updated Asset',
            'balance' => 1500.00,
        ]);

        $response->assertRedirect('/assets');

        $asset->refresh();
        $this->assertEquals('Updated Asset', $asset->name);
        $this->assertEquals(1500.00, $asset->balance);
        $this->assertEquals($this->user->id, $asset->created_by);
        $this->assertEquals($this->user->id, $asset->updated_by);
    }

    public function test_header_creation_sets_audit_fields()
    {
        $asset = Asset::create([
            'name' => 'Test Asset',
            'balance' => 1000.00,
            'unity_id' => $this->unity->id,
        ]);

        $response = $this->actingAs($this->user)->post('/templates', [
            'name' => 'Test Header',
            'type' => 'expense',
            'rule' => 'fixed',
            'default_amount' => 100.00,
            'start_date' => now()->startOfMonth()->format('Y-m-d'),
            'asset_id' => $asset->id,
        ]);

        $response->assertRedirect('/templates');

        $header = Header::where('name', 'Test Header')->first();
        $this->assertNotNull($header);
        $this->assertEquals($this->user->id, $header->created_by);
        $this->assertEquals($this->user->id, $header->updated_by);
    }

    public function test_event_creation_sets_audit_fields()
    {
        $asset = Asset::create([
            'name' => 'Test Asset',
            'balance' => 1000.00,
            'unity_id' => $this->unity->id,
            'created_by' => $this->user->id,
            'updated_by' => $this->user->id,
        ]);

        $header = Header::create([
            'name' => 'Test Header',
            'type' => EventType::Expense,
            'rule' => EventRule::Fixed,
            'default_amount' => 100.00,
            'start_date' => now()->startOfMonth(),
            'asset_id' => $asset->id,
            'unity_id' => $this->unity->id,
            'created_by' => $this->user->id,
            'updated_by' => $this->user->id,
        ]);

        $this->actingAs($this->user);

        $event = Event::create([
            'header_id' => $header->id,
            'type' => EventType::Expense,
            'name' => 'Test Event',
            'date' => now(),
            'consolidated' => false,
            'transfer_consolidated' => false,
            'unity_id' => $this->unity->id,
        ]);

        $this->assertEquals($this->user->id, $event->created_by);
        $this->assertEquals($this->user->id, $event->updated_by);
    }

    public function test_entry_creation_sets_audit_fields()
    {
        $asset = Asset::create([
            'name' => 'Test Asset',
            'balance' => 1000.00,
            'unity_id' => $this->unity->id,
            'created_by' => $this->user->id,
            'updated_by' => $this->user->id,
        ]);

        $header = Header::create([
            'name' => 'Test Header',
            'type' => EventType::Expense,
            'rule' => EventRule::Fixed,
            'default_amount' => 100.00,
            'start_date' => now()->startOfMonth(),
            'asset_id' => $asset->id,
            'unity_id' => $this->unity->id,
            'created_by' => $this->user->id,
            'updated_by' => $this->user->id,
        ]);

        $event = Event::create([
            'header_id' => $header->id,
            'type' => EventType::Expense,
            'name' => 'Test Event',
            'date' => now(),
            'consolidated' => false,
            'transfer_consolidated' => false,
            'unity_id' => $this->unity->id,
            'created_by' => $this->user->id,
            'updated_by' => $this->user->id,
        ]);

        $this->actingAs($this->user);

        $entry = Entry::create([
            'event_id' => $event->id,
            'asset_id' => $asset->id,
            'amount' => -100.00,
        ]);

        $this->assertEquals($this->user->id, $entry->created_by);
        $this->assertEquals($this->user->id, $entry->updated_by);
    }

    public function test_event_consolidation_updates_updated_by()
    {
        $asset = Asset::create([
            'name' => 'Test Asset',
            'balance' => 1000.00,
            'unity_id' => $this->unity->id,
            'created_by' => $this->user->id,
            'updated_by' => $this->user->id,
        ]);

        $header = Header::create([
            'name' => 'Test Header',
            'type' => EventType::Expense,
            'rule' => EventRule::Fixed,
            'default_amount' => 100.00,
            'start_date' => now()->startOfMonth(),
            'asset_id' => $asset->id,
            'unity_id' => $this->unity->id,
            'created_by' => $this->user->id,
            'updated_by' => $this->user->id,
        ]);

        $event = Event::create([
            'header_id' => $header->id,
            'type' => EventType::Expense,
            'name' => 'Test Event',
            'date' => now()->startOfMonth(),
            'consolidated' => false,
            'transfer_consolidated' => false,
            'unity_id' => $this->unity->id,
            'created_by' => $this->user->id,
            'updated_by' => $this->user->id,
        ]);

        Entry::create([
            'event_id' => $event->id,
            'asset_id' => $asset->id,
            'amount' => -100.00,
            'created_by' => $this->user->id,
            'updated_by' => $this->user->id,
        ]);

        $response = $this->actingAs($this->user)->patch("/api/events/{$event->id}/consolidate");

        $response->assertStatus(200);

        $event->refresh();
        $this->assertTrue($event->consolidated);
        $this->assertEquals($this->user->id, $event->updated_by);
    }
}
