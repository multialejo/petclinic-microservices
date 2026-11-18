<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class InternalTokenTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Route::middleware('internal.token')->get('/api/_internal-test', fn () => ['ok' => true]);
    }

    public function test_valid_token_passes(): void
    {
        config(['services.internal.token' => 'secret-token']);

        $this->getJson('/api/_internal-test', ['X-Internal-Token' => 'secret-token'])
            ->assertOk()
            ->assertJson(['ok' => true]);
    }

    public function test_missing_or_wrong_token_is_rejected(): void
    {
        config(['services.internal.token' => 'secret-token']);

        $this->getJson('/api/_internal-test')->assertUnauthorized()->assertJson(['message' => 'Unauthorized']);
        $this->getJson('/api/_internal-test', ['X-Internal-Token' => 'wrong'])->assertUnauthorized();
    }

    public function test_unconfigured_token_fails_closed(): void
    {
        config(['services.internal.token' => null]);

        $this->getJson('/api/_internal-test', ['X-Internal-Token' => ''])->assertUnauthorized();
    }
}
