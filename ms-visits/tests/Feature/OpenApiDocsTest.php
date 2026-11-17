<?php

namespace Tests\Feature;

use Dedoc\Scramble\Generator;
use Tests\TestCase;

class OpenApiDocsTest extends TestCase
{
    public function test_openapi_spec_documents_the_contract(): void
    {
        $spec = app(Generator::class)();

        $paths = array_keys($spec['paths']);
        foreach (['/visits'] as $path) {
            $this->assertContains($path, $paths);
        }
    }
}
