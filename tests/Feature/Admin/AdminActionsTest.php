<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use App\Models\Unity;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AdminActionsTest extends TestCase
{
    use RefreshDatabase;

    private $admin;
    private $regularUser;
    private $unity;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => Hash::make('password123'),
            'role' => 'admin',
            'approved_at' => now(),
        ]);

        $this->regularUser = User::create([
            'name' => 'Regular User',
            'email' => 'user@example.com',
            'password' => Hash::make('password123'),
            'role' => 'common',
            'approved_at' => null,
        ]);

        $this->unity = Unity::create([
            'name' => 'Test Unity',
            'description' => 'Test Description',
        ]);

        $this->admin->unities()->attach($this->unity->id);
    }

    public function test_admin_can_access_user_management()
    {
        $response = $this->actingAs($this->admin)->get('/admin/users');

        $response->assertStatus(200);
        $response->assertSee('User Management');
    }

    public function test_regular_user_cannot_access_admin_pages()
    {
        $this->regularUser->approved_at = now();
        $this->regularUser->save();
        $this->regularUser->unities()->attach($this->unity->id);

        $response = $this->actingAs($this->regularUser)->get('/admin/users');

        $response->assertStatus(403);
    }

    public function test_admin_can_approve_user()
    {
        $response = $this->actingAs($this->admin)->post("/admin/users/{$this->regularUser->id}/approve");

        $response->assertRedirect();

        $this->regularUser->refresh();
        $this->assertNotNull($this->regularUser->approved_at);
        $this->assertEquals($this->admin->id, $this->regularUser->approved_by);
    }

    public function test_admin_can_reject_user()
    {
        $userId = $this->regularUser->id;

        $response = $this->actingAs($this->admin)->post("/admin/users/{$userId}/reject");

        $response->assertRedirect('/admin/users');

        $this->assertDatabaseMissing('users', [
            'id' => $userId,
        ]);
    }

    public function test_admin_can_change_user_role()
    {
        $response = $this->actingAs($this->admin)->put("/admin/users/{$this->regularUser->id}/role", [
            'role' => 'admin',
        ]);

        $response->assertRedirect();

        $this->regularUser->refresh();
        $this->assertEquals('admin', $this->regularUser->role);
    }

    public function test_admin_cannot_change_own_role()
    {
        $response = $this->actingAs($this->admin)->put("/admin/users/{$this->admin->id}/role", [
            'role' => 'common',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('error');

        $this->admin->refresh();
        $this->assertEquals('admin', $this->admin->role);
    }

    public function test_admin_cannot_reject_themselves()
    {
        $response = $this->actingAs($this->admin)->post("/admin/users/{$this->admin->id}/reject");

        $response->assertRedirect();
        $response->assertSessionHas('error');

        $this->assertDatabaseHas('users', [
            'id' => $this->admin->id,
        ]);
    }

    public function test_admin_can_access_unity_management()
    {
        $response = $this->actingAs($this->admin)->get('/admin/unities');

        $response->assertStatus(200);
        $response->assertSee('Unity Management');
    }

    public function test_admin_can_create_unity()
    {
        $response = $this->actingAs($this->admin)->post('/admin/unities', [
            'name' => 'New Unity',
            'description' => 'New Description',
        ]);

        $response->assertRedirect('/admin/unities');

        $this->assertDatabaseHas('unities', [
            'name' => 'New Unity',
            'description' => 'New Description',
        ]);
    }

    public function test_admin_can_edit_unity()
    {
        $response = $this->actingAs($this->admin)->put("/admin/unities/{$this->unity->id}", [
            'name' => 'Updated Unity',
            'description' => 'Updated Description',
        ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('unities', [
            'id' => $this->unity->id,
            'name' => 'Updated Unity',
            'description' => 'Updated Description',
        ]);
    }

    public function test_admin_can_delete_empty_unity()
    {
        $emptyUnity = Unity::create([
            'name' => 'Empty Unity',
            'description' => 'Empty Description',
        ]);

        $response = $this->actingAs($this->admin)->delete("/admin/unities/{$emptyUnity->id}");

        $response->assertRedirect();

        $this->assertDatabaseMissing('unities', [
            'id' => $emptyUnity->id,
        ]);
    }

    public function test_admin_can_assign_user_to_unity()
    {
        $response = $this->actingAs($this->admin)->post("/admin/unities/{$this->unity->id}/assign", [
            'user_id' => $this->regularUser->id,
        ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('unity_user', [
            'unity_id' => $this->unity->id,
            'user_id' => $this->regularUser->id,
        ]);
    }

    public function test_admin_can_unassign_user_from_unity()
    {
        $this->regularUser->unities()->attach($this->unity->id);

        $response = $this->actingAs($this->admin)->post("/admin/unities/{$this->unity->id}/unassign/{$this->regularUser->id}");

        $response->assertRedirect();

        $this->assertDatabaseMissing('unity_user', [
            'unity_id' => $this->unity->id,
            'user_id' => $this->regularUser->id,
        ]);
    }

    public function test_assigning_user_to_new_unity_removes_from_old()
    {
        $unity2 = Unity::create([
            'name' => 'Unity 2',
            'description' => 'Second Unity',
        ]);

        $this->regularUser->unities()->attach($this->unity->id);

        $response = $this->actingAs($this->admin)->post("/admin/unities/{$unity2->id}/assign", [
            'user_id' => $this->regularUser->id,
        ]);

        $response->assertRedirect();

        $this->assertDatabaseMissing('unity_user', [
            'unity_id' => $this->unity->id,
            'user_id' => $this->regularUser->id,
        ]);

        $this->assertDatabaseHas('unity_user', [
            'unity_id' => $unity2->id,
            'user_id' => $this->regularUser->id,
        ]);
    }
}
