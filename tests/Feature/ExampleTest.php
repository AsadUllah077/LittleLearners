<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_application_returns_the_learning_homepage(): void
    {
        $response = $this->get('/');

        $response->assertOk();
        $response->assertSee("Let's Learn and Play!");
    }
}
