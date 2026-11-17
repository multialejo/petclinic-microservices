<?php

namespace Tests\Feature;

use Database\Seeders\CustomersSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PetsApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(CustomersSeeder::class);
    }

    public function test_show_pet_includes_type_and_owner(): void
    {
        $this->getJson('/api/pets/7')
            ->assertOk()
            ->assertExactJson(['data' => [
                'id' => 7,
                'name' => 'Samantha',
                'birth_date' => '1995-09-04',
                'type' => ['id' => 1, 'name' => 'cat'],
                'owner_id' => 6,
                'owner' => ['id' => 6, 'first_name' => 'Jean', 'last_name' => 'Coleman'],
            ]]);
    }

    public function test_unknown_pet_is_404(): void
    {
        $this->getJson('/api/pets/999')->assertNotFound();
    }

    public function test_create_pet(): void
    {
        $this->postJson('/api/owners/1/pets', ['name' => 'Toby', 'birth_date' => '2020-01-01', 'type_id' => 2])
            ->assertCreated()
            ->assertJsonPath('data.id', 14)
            ->assertJsonPath('data.owner_id', 1)
            ->assertJsonPath('data.type.name', 'dog');
    }

    public function test_create_pet_for_unknown_owner_is_404(): void
    {
        $this->postJson('/api/owners/999/pets', ['name' => 'Toby', 'birth_date' => '2020-01-01', 'type_id' => 2])
            ->assertNotFound();
    }

    public function test_create_pet_validation(): void
    {
        $this->postJson('/api/owners/1/pets', [])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['name', 'birth_date', 'type_id']);

        $this->postJson('/api/owners/1/pets', [
            'name' => 'Toby', 'birth_date' => now()->addDay()->toDateString(), 'type_id' => 99,
        ])->assertUnprocessable()->assertJsonValidationErrors(['birth_date', 'type_id']);
    }

    public function test_duplicate_pet_name_is_case_insensitive_per_owner(): void
    {
        // Owner 1 ya tiene "Leo".
        $this->postJson('/api/owners/1/pets', ['name' => 'LEO', 'birth_date' => '2020-01-01', 'type_id' => 1])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['name']);

        // Otro owner sí puede tener un "Leo".
        $this->postJson('/api/owners/2/pets', ['name' => 'Leo', 'birth_date' => '2020-01-01', 'type_id' => 1])
            ->assertCreated();
    }

    public function test_update_pet(): void
    {
        // Mantener el propio nombre (con otro casing) no es duplicado.
        $this->putJson('/api/owners/1/pets/1', ['name' => 'leo', 'birth_date' => '2001-01-01', 'type_id' => 2])
            ->assertOk()
            ->assertJsonPath('data.name', 'leo')
            ->assertJsonPath('data.type.name', 'dog');

        // Owner 6 tiene Samantha (7) y Max (8): renombrar Max a "samantha" es duplicado.
        $this->putJson('/api/owners/6/pets/8', ['name' => 'samantha', 'birth_date' => '1995-09-04', 'type_id' => 1])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['name']);
    }

    public function test_update_pet_of_another_owner_is_404(): void
    {
        $this->putJson('/api/owners/1/pets/7', ['name' => 'X', 'birth_date' => '2001-01-01', 'type_id' => 1])
            ->assertNotFound();
    }

    public function test_types_sorted_by_name(): void
    {
        $this->getJson('/api/types')
            ->assertOk()
            ->assertJsonCount(6, 'data')
            ->assertJsonPath('data.0.name', 'bird')
            ->assertJsonPath('data.5.name', 'snake');
    }
}
