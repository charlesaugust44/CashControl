<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Unity;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_application_returns_a_successful_response(): void
    {
        $user = User::factory()->create([
            'approved_at' => now(),
        ]);

        $unity = Unity::create([
            'name' => 'Test Unity',
        ]);

        $user->unities()->attach($unity->id);

        $response = $this->actingAs($user)->get('/');

        $response->assertStatus(200);
    }

    public function test_unauthenticated_users_are_redirected_to_login(): void
    {
        $response = $this->get('/');

        $response->assertRedirect('/login');
    }
}
