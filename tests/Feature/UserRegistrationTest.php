<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;

class UserRegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_using_wrong_http_method()
    {
        $methods = ['get', 'put', 'delete'];

        foreach ($methods as $method)
        {
            $response = $this->$method('/api/register');
            $response->assertStatus(405);            
        }
    }

    public function test_registration_with_empty_fields()
    {
        $response = $this->post('/api/register', [], ['Accept' => 'application/json']);

        $response->assertStatus(422);
        $response->assertJson([
            "message" => "The given data was invalid.",
            "errors" => [
                "name" => ["The name field is required."],
                "email" => ["The email field is required."],
                "password" => ["The password field is required."]
            ]
        ]);
    }

    public function test_registration_without_password_confirmation()
    {
        $body = [
            'name' => 'test',
            'email' => 'test@test.com',
            'password' => 'password'
        ];
        $response = $this->post('/api/register', $body, ['Accept' => 'application/json']);

        $response->assertStatus(422);
        $response->assertJson([
            "message" => "The given data was invalid.",
            "errors" => [
                "password" => ["The password confirmation does not match."]
            ]
        ]);
    }

    public function test_registration_with_wrong_email_format()
    {
        $body = [
            'name' => 'test',
            'email' => 'test_wrong_email_format',
            'password' => 'password',
            'password_confirmation' => 'password'
        ];
        $response = $this->post('/api/register', $body, ['Accept' => 'application/json']);

        $response->assertStatus(422);
        $response->assertJson([
            "message" => "The given data was invalid.",
            "errors" => [
                "email" => ["The email must be a valid email address."]
            ]
        ]);
    }

    public function test_registration_of_same_email_twice()
    {
        $this->test_registration_is_ok();

        $body = [
            'name' => 'test',
            'email' => 'test@test.com',
            'password' => 'password',
            'password_confirmation' => 'password'
        ];
        $response = $this->post('/api/register', $body, ['Accept' => 'application/json']);

        $response->assertStatus(422);
        $response->assertJson([
            "message" => "The given data was invalid.",
            "errors" => [
                "email" => ["The email has already been taken."]
            ]
        ]);
    }

    public function test_registration_is_ok()
    {
        $body = [
            'name' => 'test',
            'email' => 'test@test.com',
            'password' => 'password',
            'password_confirmation' => 'password'
        ];
        $response = $this->post('/api/register', $body, ['Accept' => 'application/json']);

        $response->assertStatus(201);
        $response->assertJsonStructure([
            "user" => [
                '*' => array_keys((new User())->toArray())
            ]
        ]);
    }

    public function test_registration_and_login_are_both_ok()
    {
        $this->test_registration_is_ok();

        $body = [
            'email' => 'test@test.com',
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
