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

        $this->get('/owners?lastName=Zzz')
            ->assertRedirect('/owners/find')
            ->assertSessionHasErrors(['lastName' => 'has not been found'])
            ->assertSessionHasInput('lastName', 'Zzz');

        Http::assertSent(fn (Request $r) => $r['last_name'] === 'Zzz');
    }

    public function test_search_with_one_result_redirects_to_owner(): void
    {
        Http::fake([self::GW.'/api/customers/owners*' => Http::response($this->page([['id' => 1]], 1))]);

        $this->get('/owners?lastName=Franklin')->assertRedirect('/owners/1');
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

        $this->get('/owners/6')->assertOk()->assertSee('Samantha')
            ->assertSee('id="error-message"', false)
            ->assertSee('The visits service is unavailable');
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

        $this->get('/owners/999')->assertNotFound()->assertSee('The requested page was not found.');
    }

    public function test_gateway_down_shows_503_page(): void
    {
        Http::fake([self::GW.'/*' => fn () => throw new ConnectionException('refused')]);

        $this->get('/vets.html')->assertStatus(503)
            ->assertSee('Something happened...')
            ->assertSee('An unexpected error occurred.');
    }

    public function test_api_validation_errors_are_shown_next_to_fields(): void
    {
        Http::fake([self::GW.'/api/customers/owners' => Http::response([
            'message' => 'invalid', 'errors' => [
                'last_name' => ['must not be blank'],
                'telephone' => ['Telephone must be a 10-digit number'],
            ],
        ], 422)]);

        // Los campos usan los nombres del monolito (firstName...) y se traducen a la API (first_name...).
        $this->from('/owners/new')
            ->post('/owners/new', ['firstName' => 'Ana', 'telephone' => '12'])
            ->assertRedirect('/owners/new')
            ->assertSessionHasErrors(['lastName' => 'must not be blank', 'telephone'])
            ->assertSessionHasInput('firstName', 'Ana');

        Http::assertSent(fn (Request $r) => $r['first_name'] === 'Ana' && $r['telephone'] === '12');
    }

    public function test_booking_a_visit_sends_owner_and_pet_and_redirects(): void
    {
        Http::fake([self::GW.'/api/visits' => Http::response(['data' => ['id' => 5]], 201)]);

        $this->post('/owners/6/pets/7/visits/new', ['date' => '2099-01-01', 'description' => 'control'])
            ->assertRedirect('/owners/6')
            ->assertSessionHas('message', 'Your visit has been booked');

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

    public function test_pet_form_maps_fields_and_errors_like_the_monolith(): void
    {
        Http::fake([
            self::GW.'/api/customers/types' => Http::response(['data' => [['id' => 5, 'name' => 'bird'], ['id' => 1, 'name' => 'cat']]]),
            self::GW.'/api/customers/owners/6/pets' => Http::response([
                'message' => 'invalid', 'errors' => ['name' => ['is already in use'], 'birth_date' => ['invalid date']],
            ], 422),
        ]);

        // Como en el monolito, el select envía el nombre del tipo; el frontend lo traduce a type_id.
        $this->from('/owners/6/pets/new')
            ->post('/owners/6/pets/new', ['name' => 'max', 'birthDate' => '2099-01-01', 'type' => 'cat'])
            ->assertSessionHasErrors(['name' => 'is already in use', 'birthDate' => 'invalid date']);

        Http::assertSent(fn (Request $r) => $r->method() === 'POST'
            && $r['birth_date'] === '2099-01-01' && $r['type_id'] === 1);
    }

    public function test_layout_matches_the_monolith(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('<title>PetClinic :: a Spring Framework demonstration</title>', false)
            ->assertSee('/resources/css/petclinic.css', false)
            ->assertSee('/webjars/font-awesome/4.7.0/css/font-awesome.min.css', false)
            ->assertSee('<h2>Welcome</h2>', false)
            ->assertSee('Find Owners')
            ->assertSee('Veterinarians');
    }

    public function test_oups_shows_the_monolith_error_page(): void
    {
        $this->get('/oups')->assertStatus(500)
            ->assertSee('Something happened...')
            ->assertSee('An internal server error occurred.');
    }
}
