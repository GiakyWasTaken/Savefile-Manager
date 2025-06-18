<?php

namespace Tests\Feature;

use Tests\TestCase;
use Mockery;

class HealthCheckTest extends TestCase
{
    public function test_health_check()
    {
        $response = $this->getJson('/api/health');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'server',
                'database',
                'filesystem',
                'timestamp',
                'overall'
            ])
            ->assertJson([
                'server' => true,
                'database' => true,
                'filesystem' => true,
                'overall' => true
            ]);
    }
}
