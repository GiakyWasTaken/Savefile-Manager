<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Testing\Fluent\AssertableJson;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;
use App\Models\User;
use Faker\Factory;

class UserTest extends TestCase
{

    use DatabaseTransactions;

    public function test_user_register()
    {
        $faker = Factory::create();

        $name = $faker->name;
        $email = $faker->email;
        $password = $faker->password;

        $response = $this->post('api/register', [
            'name' => $name,
            'email' => $email,
            'password' => $password,
            'password_confirmation' => $password,
        ]);

        $response
            ->assertStatus(201)
            ->assertJson(fn (AssertableJson $json) =>
                $json->where('user.name', $name)
                    ->where('user.email', $email)
                    ->whereType('token', 'string')
                    ->etc()
            );

        $this->assertDatabaseHas('users', [
            'name' => $name,
            'email' => $email
        ]);
    }

    public function test_user_login()
    {
        $password = Factory::create()->password;

        $user = User::factory()->create([
            'password' => Hash::make($password)
        ]);

        $response = $this->post('api/login', [
            'email' => $user->email,
            'password' => $password,
        ]);

        $response
            ->assertStatus(200)
            ->assertJson(fn (AssertableJson $json) =>
                $json->whereType('token', 'string')
            );
    }

    public function test_user_login_invalid()
    {
        $password = Factory::create()->password;

        $user = User::factory()->create([
            'password' => Hash::make($password)
        ]);

        $response = $this->post('api/login', [
            'email' => $user->email,
            'password' => 'invalid-password',
        ]);

        $response->assertStatus(401);
    }

    public function test_get_user()
    {
        $user = User::factory()->create();
        $token = $user->createToken('user_token')->accessToken;

        $response = $this->get('api/user', [
            'Authorization' => "Bearer $token"
        ]);

        $response
            ->assertStatus(200)
            ->assertJson(fn (AssertableJson $json) =>
                $json->where('id', $user->id)
                    ->where('name', $user->name)
                    ->where('email', $user->email)
                    ->etc()
            );
    }

    public function test_user_logout()
    {
        $user = User::factory()->create();
        $token = $user->createToken('user_token')->accessToken;

        $response = $this->get('api/logout', [
            'Authorization' => "Bearer $token"
        ]);

        $response->assertStatus(200);
    }
}
