<?php

namespace Tests\Feature;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/** El frontend no tiene BD: todo el backend (API Gateway) se simula con Http::fake(). */
class FrontendTest extends TestCase
{
    private const GW = 'http://gateway.test';

    protected function setUp(): void
    {
        parent::setUp();
        config(['services.gateway.url' => self::GW]);
    }

    private function page(array $data, int $total, int $current = 1, int $last = 1): array
    {
        return ['data' => $data, 'meta' => ['total' => $total, 'current_page' => $current, 'last_page' => $last]];
    }

    private function owner(): array
    {
        return ['data' => [
            'id' => 6, 'first_name' => 'Jean', 'last_name' => 'Coleman', 'address' => '105 N. Lake St.',
            'city' => 'Monona', 'telephone' => '6085552654',
            'pets' => [['id' => 7, 'name' => 'Samantha', 'birth_date' => '1995-09-04', 'type' => ['id' => 1, 'name' => 'cat'], 'owner_id' => 6]],
        ]];
    }

    public function test_search_with_no_results_returns_to_form_with_message(): void
    {
        Http::fake([self::GW.'/api/customers/owners*' => Http::response($this->page([], 0))]);

        $this->get('/owners?last_name=Zzz')
            ->assertRedirect('/owners/find')
            ->assertSessionHasErrors('last_name');
    }

    public function test_search_with_one_result_redirects_to_owner(): void
    {
        Http::fake([self::GW.'/api/customers/owners*' => Http::response($this->page([['id' => 1]], 1))]);

        $this->get('/owners?last_name=Franklin')->assertRedirect('/owners/1');
    }

    public function test_search_page_out_of_range_goes_back_to_page_one(): void
    {
        Http::fake([self::GW.'/api/customers/owners*' => Http::response($this->page([], 10, 9, 2))]);

        $this->get('/owners?page=9')->assertRedirect('/owners?page=1');
    }

    public function test_owner_page_composes_customers_and_visits(): void
    {
        Http::fake([
            self::GW.'/api/customers/owners/6' => Http::response($this->owner()),
            self::GW.'/api/visits*' => Http::response(['data' => [
                ['id' => 1, 'pet_id' => 7, 'date' => '2010-03-04', 'description' => 'rabies shot'],
            ]]),
        ]);

        $this->get('/owners/6')->assertOk()->assertSee('Jean Coleman')->assertSee('Samantha')->assertSee('rabies shot');

        Http::assertSent(fn (Request $r) => str_starts_with($r->url(), self::GW.'/api/visits')
            && $r['pet_ids'] === '7');
    }

    public function test_owner_page_still_renders_when_visits_service_is_down(): void
    {
        Http::fake([
            self::GW.'/api/customers/owners/6' => Http::response($this->owner()),
            self::GW.'/api/visits*' => Http::response(['message' => 'Service unavailable'], 503),
        ]);

        $this->get('/owners/6')->assertOk()->assertSee('Samantha')->assertSee('servicio de visitas no está disponible');
    }

    public function test_owner_without_pets_does_not_call_visits(): void
    {
        $owner = $this->owner();
        $owner['data']['pets'] = [];
        Http::fake([self::GW.'/api/customers/owners/6' => Http::response($owner)]);

        $this->get('/owners/6')->assertOk();
        Http::assertSentCount(1);
    }

    public function test_unknown_owner_is_404(): void
    {
        Http::fake([self::GW.'/*' => Http::response(['message' => 'Not Found'], 404)]);

        $this->get('/owners/999')->assertNotFound();
    }

    public function test_gateway_down_shows_503_page(): void
    {
        Http::fake([self::GW.'/*' => fn () => throw new ConnectionException('refused')]);

        $this->get('/vets.html')->assertStatus(503)->assertSee('Servicio no disponible');
    }

    public function test_api_validation_errors_are_shown_next_to_fields(): void
    {
        Http::fake([self::GW.'/api/customers/owners' => Http::response([
            'message' => 'invalid', 'errors' => ['telephone' => ['El teléfono debe tener exactamente 10 dígitos.']],
        ], 422)]);

        $this->from('/owners/new')
            ->post('/owners/new', ['first_name' => 'Ana', 'telephone' => '12'])
            ->assertRedirect('/owners/new')
            ->assertSessionHasErrors('telephone')
            ->assertSessionHasInput('first_name', 'Ana');
    }

    public function test_booking_a_visit_sends_owner_and_pet_and_redirects(): void
    {
        Http::fake([self::GW.'/api/visits' => Http::response(['data' => ['id' => 5]], 201)]);

        $this->post('/owners/6/pets/7/visits/new', ['date' => '2099-01-01', 'description' => 'control'])
            ->assertRedirect('/owners/6')
            ->assertSessionHas('status');

        Http::assertSent(fn (Request $r) => $r->method() === 'POST'
            && $r['owner_id'] === 6 && $r['pet_id'] === 7 && $r['date'] === '2099-01-01');
    }

    public function test_visit_form_defaults_to_tomorrow(): void
    {
        Http::fake([
            self::GW.'/api/customers/pets/7' => Http::response(['data' => [
                'id' => 7, 'name' => 'Samantha', 'birth_date' => '1995-09-04', 'type' => ['id' => 1, 'name' => 'cat'],
                'owner_id' => 6, 'owner' => ['id' => 6, 'first_name' => 'Jean', 'last_name' => 'Coleman'],
            ]]),
            self::GW.'/api/visits*' => Http::response(['data' => []]),
        ]);

        $this->get('/owners/6/pets/7/visits/new')->assertOk()->assertSee(now()->addDay()->toDateString());
        $this->get('/owners/1/pets/7/visits/new')->assertNotFound(); // pet de otro owner
    }
}
