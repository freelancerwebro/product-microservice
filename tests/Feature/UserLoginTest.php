<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;

class UserLoginTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_using_wrong_http_method()
    {
        $methods = ['get', 'put', 'delete'];

        foreach ($methods as $method)
        {
            $response = $this->$method('/api/login');
            $response->assertStatus(405);            
        }
    }

    public function test_login_with_empty_credentials()
    {
        $response = $this->post('/api/login', [], ['Accept' => 'application/json']);

        $response->assertStatus(422);
        $response->assertJson([
            "message" => "The given data was invalid.",
            "errors" => [
                "email" => ["The email field is required."],
                "password" => ["The password field is required."]
            ]
        ]);
    }

    public function test_login_with_non_existing_credentials()
    {
        $body = [
            'email' => 'wrong_email@test.com',
            'password' => 'wrong_password'
        ];
        $response = $this->post('/api/login', $body, ['Accept' => 'application/json']);

        $response->assertStatus(401);
        $response->assertJson([
            "message" => "Bad credentials"
        ]);
    }

    public function test_login_with_wrong_email_format()
    {
        $body = [
            'email' => 'wrong_email_format',
            'password' => 'wrong_password'
        ];
        $response = $this->post('/api/login', $body, ['Accept' => 'application/json']);

        $response->assertStatus(422);
        $response->assertJson([
            "message" => "The given data was invalid.",
            "errors" => [
                "email" => ["The email must be a valid email address."]
            ]
        ]);
    }

    public function test_login_is_ok()
    {
        $user = User::factory()->create();

        $body = [
            'email' => $user->email,
            'password' => 'password'
        ];
        $response = $this->post('/api/login', $body, ['Accept' => 'application/json']);

        $response->assertStatus(200);
        $response->assertJsonStructure([
            "user" => [
                '*' => array_keys((new User())->toArray())
            ]
        ]);
    }
}
