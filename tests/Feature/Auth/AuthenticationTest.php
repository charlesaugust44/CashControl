<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use App\Models\Unity;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_page_is_accessible()
    {
        $response = $this->get('/login');

        $response->assertStatus(200);
    }

    public function test_register_page_is_accessible()
    {
        $response = $this->get('/register');

        $response->assertStatus(200);
    }

    public function test_user_can_register()
    {
        $response = $this->post('/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertRedirect('/login');

        $this->assertDatabaseHas('users', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'role' => 'common',
            'approved_at' => null,
        ]);
    }

    public function test_user_cannot_login_without_approval()
    {
        $user = User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => Hash::make('password123'),
            'role' => 'common',
            'approved_at' => null,
        ]);

        $response = $this->post('/login', [
            'email' => 'test@example.com',
            'password' => 'password123',
        ]);

        $response->assertRedirect('/pending-approval');
        $this->assertAuthenticatedAs($user);
    }

    public function test_user_cannot_login_without_unity()
    {
        $user = User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => Hash::make('password123'),
            'role' => 'common',
            'approved_at' => now(),
        ]);

        $response = $this->post('/login', [
            'email' => 'test@example.com',
            'password' => 'password123',
        ]);

        // User is authenticated but should be redirected to no-unity page when trying to access protected routes
        $this->assertAuthenticatedAs($user);
        
        // Try to access a protected route
        $response = $this->get('/');
        $response->assertRedirect('/no-unity');
    }

    public function test_approved_user_with_unity_can_login()
    {
        $user = User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => Hash::make('password123'),
            'role' => 'common',
            'approved_at' => now(),
        ]);

        $unity = Unity::create([
            'name' => 'Test Unity',
            'description' => 'Test Description',
        ]);

        $user->unities()->attach($unity->id);

        $response = $this->post('/login', [
            'email' => 'test@example.com',
            'password' => 'password123',
        ]);

        $response->assertRedirect('/');
        $this->assertAuthenticatedAs($user);
    }

    public function test_user_can_logout()
    {
        $user = User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => Hash::make('password123'),
            'role' => 'common',
            'approved_at' => now(),
        ]);

        $unity = Unity::create([
            'name' => 'Test Unity',
            'description' => 'Test Description',
        ]);

        $user->unities()->attach($unity->id);

        $this->actingAs($user);

        $response = $this->post('/logout');

        $response->assertRedirect('/login');
        $this->assertGuest();
    }

    public function test_unauthenticated_user_is_redirected_to_login()
    {
        $response = $this->get('/');

        $response->assertRedirect('/login');
    }

    public function test_pending_user_is_redirected_to_pending_page()
    {
        $user = User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => Hash::make('password123'),
            'role' => 'common',
            'approved_at' => null,
        ]);

        $response = $this->actingAs($user)->get('/');

        $response->assertRedirect('/pending-approval');
    }

    public function test_user_without_unity_is_redirected_to_no_unity_page()
    {
        $user = User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => Hash::make('password123'),
            'role' => 'common',
            'approved_at' => now(),
        ]);

        $response = $this->actingAs($user)->get('/');

        $response->assertRedirect('/no-unity');
    }

    public function test_pending_approval_page_is_accessible()
    {
        $user = User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => Hash::make('password123'),
            'role' => 'common',
            'approved_at' => null,
        ]);

        $response = $this->actingAs($user)->get('/pending-approval');

        $response->assertStatus(200);
    }

    public function test_no_unity_page_is_accessible()
    {
        $user = User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => Hash::make('password123'),
            'role' => 'common',
            'approved_at' => now(),
        ]);

        $response = $this->actingAs($user)->get('/no-unity');

        $response->assertStatus(200);
    }
}
