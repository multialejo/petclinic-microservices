<?php

namespace Tests\Feature;

use App\Events\VisitCreated;
use App\Listeners\RecordVisitAudit;
use Database\Seeders\VisitsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class VisitsApiTest extends TestCase
{
    use RefreshDatabase;

    private const PET_URL = 'http://ms-customers.test/api/pets/*';

    protected function setUp(): void
    {
        parent::setUp();
        config([
            'services.customers.url' => 'http://ms-customers.test',
            'services.customers.token' => 'test-token',
        ]);
        $this->seed(VisitsSeeder::class);
    }

    private function petResponse(int $petId = 7, int $ownerId = 6): array
    {
        return ['data' => [
            'id' => $petId, 'name' => 'Samantha', 'birth_date' => '1995-09-04',
            'type' => ['id' => 1, 'name' => 'cat'], 'owner_id' => $ownerId,
            'owner' => ['id' => $ownerId, 'first_name' => 'Jean', 'last_name' => 'Coleman'],
        ]];
    }

    private function payload(array $overrides = []): array
    {
        return [
            'owner_id' => 6,
            'pet_id' => 7,
            'date' => now()->addDay()->toDateString(),
            'description' => 'control anual',
            ...$overrides,
        ];
    }

    // ------------------------------------------------------------ GET /api/visits

    public function test_lists_visits_for_several_pets_sorted_by_pet_then_date(): void
    {
        $this->getJson('/api/visits?pet_ids=8,7')
            ->assertOk()
            ->assertJsonCount(4, 'data')
            ->assertJsonPath('data.0', ['id' => 4, 'pet_id' => 7, 'date' => '2008-09-04', 'description' => 'spayed'])
            ->assertJsonPath('data.1.id', 1)
            ->assertJsonPath('data.2.id', 3)
            ->assertJsonPath('data.3.id', 2);
    }

    public function test_pet_without_visits_is_simply_absent(): void
    {
        $this->getJson('/api/visits?pet_ids=1,7')->assertOk()->assertJsonCount(2, 'data');
    }

    public function test_pet_ids_is_required_and_validated(): void
    {
        $this->getJson('/api/visits')->assertUnprocessable()->assertJsonValidationErrors(['pet_ids']);
        $this->getJson('/api/visits?pet_ids=')->assertUnprocessable();
        $this->getJson('/api/visits?pet_ids=1,abc')->assertUnprocessable();
        $this->getJson('/api/visits?pet_ids='.implode(',', range(1, 51)))->assertUnprocessable();
    }

    public function test_reading_visits_never_calls_customers(): void
    {
        Http::fake();
        $this->getJson('/api/visits?pet_ids=7')->assertOk();
        Http::assertNothingSent();
    }

    // ----------------------------------------------------------- POST /api/visits

    public function test_creates_visit_after_verifying_pet_with_internal_token(): void
    {
        Event::fake([VisitCreated::class]);
        Http::fake([self::PET_URL => Http::response($this->petResponse())]);

        $date = now()->addDays(3)->toDateString();

        $this->postJson('/api/visits', $this->payload(['date' => $date]))
            ->assertCreated()
            ->assertJsonPath('data.pet_id', 7)
            ->assertJsonPath('data.date', $date)
            ->assertJsonPath('data.description', 'control anual');

        $this->assertDatabaseHas('visits', ['pet_id' => 7, 'description' => 'control anual']);

        Http::assertSent(fn (Request $r) => $r->url() === 'http://ms-customers.test/api/pets/7'
            && $r->hasHeader('X-Internal-Token', 'test-token'));

        Event::assertDispatched(VisitCreated::class, fn (VisitCreated $e) => $e->ownerId === 6
            && $e->petName === 'Samantha' && $e->ownerName === 'Jean Coleman');
    }

    public function test_visit_created_listener_is_queued(): void
    {
        Event::fake();
        Http::fake([self::PET_URL => Http::response($this->petResponse())]);

        $this->postJson('/api/visits', $this->payload())->assertCreated();

        Event::assertListening(VisitCreated::class, RecordVisitAudit::class);
    }

    public function test_listener_writes_audit_record(): void
    {
        // QUEUE_CONNECTION=sync en phpunit.xml: el listener se ejecuta en línea.
        Http::fake([self::PET_URL => Http::response($this->petResponse())]);

        $this->postJson('/api/visits', $this->payload())->assertCreated();

        $this->assertDatabaseHas('visit_audits', ['pet_id' => 7, 'owner_id' => 6, 'event' => 'visit.created']);
    }

    public function test_unknown_pet_is_404(): void
    {
        Http::fake([self::PET_URL => Http::response(['message' => 'Not Found'], 404)]);

        $this->postJson('/api/visits', $this->payload(['pet_id' => 999]))
            ->assertNotFound()
            ->assertJson(['message' => 'Pet not found']);

        $this->assertDatabaseCount('visits', 4);
        Http::assertSentCount(1); // un 404 no se reintenta
    }

    public function test_pet_of_another_owner_is_422(): void
    {
        Http::fake([self::PET_URL => Http::response($this->petResponse(7, 6))]);

        $this->postJson('/api/visits', $this->payload(['owner_id' => 1]))
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['pet_id']);

        $this->assertDatabaseCount('visits', 4);
    }

    public function test_customers_server_error_is_503(): void
    {
        Http::fake([self::PET_URL => Http::response(null, 500)]);

        $this->postJson('/api/visits', $this->payload())
            ->assertStatus(503)
            ->assertJson(['message' => 'Customers service unavailable']);
    }

    public function test_customers_down_is_503_after_one_retry(): void
    {
        $attempts = 0;
        Http::fake([self::PET_URL => function () use (&$attempts) {
            $attempts++;
            throw new ConnectionException('Connection refused');
        }]);

        $this->postJson('/api/visits', $this->payload())
            ->assertStatus(503)
            ->assertJson(['message' => 'Customers service unavailable']);

        $this->assertSame(2, $attempts, 'retry(2) = 2 intentos en total');
        $this->assertDatabaseCount('visits', 4);
    }

    public function test_connection_error_then_success_is_retried(): void
    {
        Http::fakeSequence(self::PET_URL)
            ->pushFailedConnection()
            ->push($this->petResponse());

        $this->postJson('/api/visits', $this->payload())->assertCreated();
    }

    public function test_unexpected_4xx_is_503(): void
    {
        Http::fake([self::PET_URL => Http::response(['message' => 'Unauthorized'], 401)]);

        $this->postJson('/api/visits', $this->payload())->assertStatus(503);
    }

    public function test_validation_happens_before_calling_customers(): void
    {
        Http::fake();

        $this->postJson('/api/visits', $this->payload(['date' => now()->toDateString(), 'description' => '']))
            ->assertUnprocessable()
            ->assertJsonPath('errors.date.0', 'Visit date must be in the future')
            ->assertJsonPath('errors.description.0', 'must not be blank');

        $this->postJson('/api/visits', [])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['owner_id', 'pet_id', 'date', 'description']);

        Http::assertNothingSent();
    }
}
