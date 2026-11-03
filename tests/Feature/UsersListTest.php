<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UsersListTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_sees_registered_users_without_sensitive_fields(): void
    {
        $viewer = User::factory()->create([
            'name' => 'Usuario de prueba',
            'email' => 'viewer@example.test',
            'password' => 'Secret123',
        ]);
        User::factory()->create([
            'name' => 'Otra persona',
            'email' => 'other@example.test',
            'password' => 'Hidden123',
        ]);

        $response = $this->actingAs($viewer)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Usuarios registrados')
            ->assertSee('Usuario de prueba')
            ->assertSee('viewer@example.test')
            ->assertSee('Otra persona')
            ->assertSee('other@example.test')
            ->assertDontSee('Secret123')
            ->assertDontSee('Hidden123')
            ->assertDontSee($viewer->password);

        $this->assertSame(
            ['id', 'name', 'email', 'created_at'],
            array_keys($response->viewData('users')->items()[0]->getAttributes())
        );
    }

    public function test_user_list_is_paginated(): void
    {
        $viewer = User::factory()->create();
        User::factory()->count(10)->create();

        $firstPage = $this->actingAs($viewer)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('page=2', false);

        $this->assertCount(10, $firstPage->viewData('users')->items());

        $this->get(route('dashboard', ['page' => 2]))
            ->assertOk();
    }

    public function test_guest_cannot_see_registered_users(): void
    {
        $this->get(route('dashboard'))->assertRedirect(route('login'));
    }
}
