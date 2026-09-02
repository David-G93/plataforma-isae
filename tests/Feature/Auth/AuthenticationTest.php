<?php

namespace Tests\Feature\Auth;

use App\Models\Person;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\RateLimiter;
use Laravel\Fortify\Features;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_screen_can_be_rendered()
    {
        $response = $this->get(route('login'));

        $response->assertOk();
    }

    public function test_users_can_authenticate_using_dni()
    {
        $person = Person::factory()->create([
            'dni' => '30111222',
        ]);

        $user = User::factory()->create([
            'person_id' => $person->id,
            'is_active' => true,
        ]);

        $response = $this->post(route('login.store'), [
            'dni' => $person->dni,
            'password' => 'password',
        ]);

        $this->assertAuthenticatedAs($user);
        $response->assertRedirect(route('dashboard', absolute: false));
    }

    public function test_users_with_two_factor_enabled_are_redirected_to_two_factor_challenge()
    {
        $this->skipUnlessFortifyHas(Features::twoFactorAuthentication());

        Features::twoFactorAuthentication([
            'confirm' => true,
            'confirmPassword' => true,
        ]);

        $person = Person::factory()->create([
            'dni' => '30111223',
        ]);

        $user = User::factory()
            ->withTwoFactor()
            ->create([
                'person_id' => $person->id,
                'is_active' => true,
            ]);

        $response = $this->post(route('login'), [
            'dni' => $person->dni,
            'password' => 'password',
        ]);

        $response->assertRedirect(route('two-factor.login'));
        $response->assertSessionHas('login.id', $user->id);
        $this->assertGuest();
    }

    public function test_users_can_not_authenticate_with_invalid_password()
    {
        $person = Person::factory()->create([
            'dni' => '30111224',
        ]);

        User::factory()->create([
            'person_id' => $person->id,
            'is_active' => true,
        ]);

        $this->post(route('login.store'), [
            'dni' => $person->dni,
            'password' => 'wrong-password',
        ]);

        $this->assertGuest();
    }

    public function test_inactive_users_can_not_authenticate()
    {
        $person = Person::factory()->create([
            'dni' => '30111225',
        ]);

        User::factory()->create([
            'person_id' => $person->id,
            'is_active' => false,
        ]);

        $this->post(route('login.store'), [
            'dni' => $person->dni,
            'password' => 'password',
        ]);

        $this->assertGuest();
    }

    public function test_people_without_user_accounts_can_not_authenticate()
    {
        $person = Person::factory()->create([
            'dni' => '30111226',
        ]);

        $this->post(route('login.store'), [
            'dni' => $person->dni,
            'password' => 'password',
        ]);

        $this->assertGuest();
    }

    public function test_users_can_logout()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('logout'));

        $response->assertRedirect(route('home'));

        $this->assertGuest();
    }

    public function test_users_are_rate_limited()
    {
        $person = Person::factory()->create([
            'dni' => '30111227',
        ]);

        User::factory()->create([
            'person_id' => $person->id,
            'is_active' => true,
        ]);

        RateLimiter::increment(
            md5('login'.implode('|', [$person->dni, '127.0.0.1'])),
            amount: 5,
        );

        $response = $this->post(route('login.store'), [
            'dni' => $person->dni,
            'password' => 'wrong-password',
        ]);

        $response->assertTooManyRequests();
    }
}