<?php

namespace Tests\Feature;

use Database\Seeders\VetsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VetsApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(VetsSeeder::class);
    }

    public function test_lists_first_page_of_five_vets_with_pagination_meta(): void
    {
        $this->getJson('/api/vets')
            ->assertOk()
            ->assertJsonCount(5, 'data')
            ->assertJsonPath('meta.total', 6)
            ->assertJsonPath('meta.per_page', 5)
            ->assertJsonPath('meta.last_page', 2)
            ->assertJsonStructure([
                'data' => [['id', 'first_name', 'last_name', 'specialties']],
                'links' => ['first', 'last', 'prev', 'next'],
                'meta' => ['current_page', 'total', 'per_page'],
            ])
            ->assertJsonPath('data.0.first_name', 'James')
            ->assertJsonPath('data.0.specialties', []);
    }

    public function test_second_page_contains_remaining_vet(): void
    {
        $this->getJson('/api/vets?page=2')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.last_name', 'Jenkins');
    }

    public function test_specialties_are_sorted_by_name(): void
    {
        // Linda Douglas (id 3): surgery + dentistry -> dentistry primero.
        $this->getJson('/api/vets')
            ->assertJsonPath('data.2.id', 3)
            ->assertJsonPath('data.2.specialties.0.name', 'dentistry')
            ->assertJsonPath('data.2.specialties.1.name', 'surgery');
    }

    public function test_out_of_range_page_returns_empty_data(): void
    {
        $this->getJson('/api/vets?page=9')
            ->assertOk()
            ->assertJsonCount(0, 'data')
            ->assertJsonPath('meta.total', 6);
    }
}
