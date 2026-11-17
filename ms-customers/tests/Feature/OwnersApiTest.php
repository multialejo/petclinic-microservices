<?php

namespace Tests\Feature;

use Database\Seeders\CustomersSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OwnersApiTest extends TestCase
{
    use RefreshDatabase;

    private array $valid = [
        'first_name' => 'Ana',
        'last_name' => 'Torres',
        'address' => 'Av. Siempre Viva 742',
        'city' => 'Quito',
        'telephone' => '0991234567',
    ];

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(CustomersSeeder::class);
    }

    public function test_search_without_last_name_returns_all_paginated(): void
    {
        $this->getJson('/api/owners')
            ->assertOk()
            ->assertJsonCount(5, 'data')
            ->assertJsonPath('meta.total', 10)
            ->assertJsonPath('meta.per_page', 5);
    }

    public function test_search_by_last_name_prefix_includes_pet_names(): void
    {
        $this->getJson('/api/owners?last_name=Dav')
            ->assertOk()
            ->assertJsonPath('meta.total', 2)
            ->assertJsonPath('data.0.last_name', 'Davis')
            ->assertJsonPath('data.0.pets.0.name', 'Basil')
            ->assertJsonMissingPath('data.0.pets.0.birth_date');
    }

    public function test_search_with_no_match_returns_empty(): void
    {
        $this->getJson('/api/owners?last_name=Zzz')->assertOk()->assertJsonPath('meta.total', 0);
    }

    public function test_search_treats_wildcards_literally(): void
    {
        $this->getJson('/api/owners?last_name=%25')->assertOk()->assertJsonPath('meta.total', 0);
    }

    public function test_pagination_links_use_public_prefix_from_gateway(): void
    {
        $viaGateway = $this->getJson('/api/owners?page=1', ['X-Forwarded-Prefix' => '/api/customers']);
        $this->assertStringEndsWith('/api/customers/owners?page=2', $viaGateway->json('links.next'));

        $direct = $this->getJson('/api/owners?page=1');
        $this->assertStringEndsWith('/api/owners?page=2', $direct->json('links.next'));
        $this->assertStringNotContainsString('customers', $direct->json('links.next'));
    }

    public function test_show_owner_with_pets_sorted_by_name_and_type(): void
    {
        // Owner 6 (Jean Coleman): Samantha y Max -> Max primero.
        $this->getJson('/api/owners/6')
            ->assertOk()
            ->assertJsonPath('data.first_name', 'Jean')
            ->assertJsonPath('data.pets.0.name', 'Max')
            ->assertJsonPath('data.pets.0.birth_date', '1995-09-04')
            ->assertJsonPath('data.pets.0.type.name', 'cat')
            ->assertJsonPath('data.pets.1.name', 'Samantha');
    }

    public function test_unknown_owner_is_404_json(): void
    {
        $this->getJson('/api/owners/999')->assertNotFound()->assertJsonStructure(['message']);
    }

    public function test_create_owner(): void
    {
        $this->postJson('/api/owners', $this->valid)
            ->assertCreated()
            ->assertJsonPath('data.id', 11)
            ->assertJsonPath('data.last_name', 'Torres')
            ->assertJsonPath('data.pets', []);

        $this->assertDatabaseHas('owners', ['telephone' => '0991234567']);
    }

    public function test_create_owner_validation_errors(): void
    {
        $this->postJson('/api/owners', ['telephone' => '12345'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['first_name', 'last_name', 'address', 'city', 'telephone'])
            ->assertJsonPath('errors.first_name.0', 'must not be blank')
            ->assertJsonPath('errors.telephone.0', 'Telephone must be a 10-digit number');

        $this->postJson('/api/owners', [...$this->valid, 'first_name' => str_repeat('a', 31)])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['first_name']);
    }

    public function test_update_owner(): void
    {
        $this->putJson('/api/owners/1', [...$this->valid, 'first_name' => 'Jorge'])
            ->assertOk()
            ->assertJsonPath('data.first_name', 'Jorge')
            ->assertJsonPath('data.pets.0.name', 'Leo');

        $this->putJson('/api/owners/999', $this->valid)->assertNotFound();
        $this->putJson('/api/owners/1', [...$this->valid, 'telephone' => 'abc'])->assertUnprocessable();
    }
}
