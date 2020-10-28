<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HolidayControllerTest extends TestCase
{

    public function testCreateRoute()
    {
        $response = $this->get('/');
        $response->assertStatus(200);
        $response->assertViewHas('countries');
    }

    public function testShowRoute()
    {
        $response = $this->get('/show');
        $response->assertStatus(302);
    }
}
