<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Illuminate\Http\Response;

class AuthTest extends TestCase
{
    /**
     * Test userLoggedInTest
     *
     * @return json
     */
    public function test_userLoggedInTest()
    {
        $this->json('get', '/api/userLoggedInTest')
            ->assertStatus(200)
            ->assertExactJson([
                'success' => false,
                'message' => "Is NOT logged in",
                'data' => false
            ]);
    }

    /* User Login Tests */

    /**
     * Test userLogin Missing Credentials
     *
     * @return json
     */
    public function test_userLoginMissingCredentials()
    {
        $this->json('post', '/api/userLogin')
            ->assertStatus(200)
            ->assertExactJson([
                'success' => false,
                'message' => "Missing or incorrect cridentials",
                'data'    => false
            ]);
    }

    /**
     * Test userLogin Incorrect Credentials
     *
     * @return json
     */
    public function test_userLoginIncorrectCredentials()
    {
        $this->json('post', '/api/userLogin', [
            'username' => 'Nameuser',
            'password' => 'Wordpass'
        ])
            ->assertStatus(200)
            ->assertExactJson([
                'success' => false,
                'message' => "Missing or incorrect cridentials",
                'data'    => false
            ]);
    }
}
