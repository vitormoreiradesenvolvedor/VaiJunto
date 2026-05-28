<?php

use App\Models\User;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

// TC-01: login com email @estudante.ufla.br válido
it('authenticates user with valid ufla institutional email', function () {
    $socialiteUser = Mockery::mock('Laravel\Socialite\Two\User');
    $socialiteUser->shouldReceive('getEmail')->andReturn('joao@estudante.ufla.br');
    $socialiteUser->shouldReceive('getName')->andReturn('João Silva');
    $socialiteUser->shouldReceive('getAvatar')->andReturn('https://example.com/avatar.jpg');

    Socialite::shouldReceive('driver->stateless->redirectUrl->user')->andReturn($socialiteUser);

    $response = $this->get('/auth/google/callback?code=fake_code');

    $response->assertRedirect('/dashboard');
    $this->assertAuthenticated();
    $this->assertDatabaseHas('users', ['email' => 'joao@estudante.ufla.br']);
});

// TC-01 variante: login com email @ufla.br (professores/servidores)
it('authenticates user with @ufla.br email', function () {
    $socialiteUser = Mockery::mock('Laravel\Socialite\Two\User');
    $socialiteUser->shouldReceive('getEmail')->andReturn('professor@ufla.br');
    $socialiteUser->shouldReceive('getName')->andReturn('Prof. Maria');
    $socialiteUser->shouldReceive('getAvatar')->andReturn('https://example.com/avatar.jpg');

    Socialite::shouldReceive('driver->stateless->redirectUrl->user')->andReturn($socialiteUser);

    $response = $this->get('/auth/google/callback?code=fake_code');

    $response->assertRedirect('/dashboard');
    $this->assertAuthenticated();
});

// TC-02: login com email externo é rejeitado
it('rejects authentication with non-ufla email', function () {
    $socialiteUser = Mockery::mock('Laravel\Socialite\Two\User');
    $socialiteUser->shouldReceive('getEmail')->andReturn('usuario@gmail.com');
    $socialiteUser->shouldReceive('getName')->andReturn('Externo');
    $socialiteUser->shouldReceive('getAvatar')->andReturn('');

    Socialite::shouldReceive('driver->stateless->redirectUrl->user')->andReturn($socialiteUser);

    $response = $this->get('/auth/google/callback?code=fake_code');

    $response->assertRedirect('/login');
    $response->assertSessionHas('error', 'unauthorized_domain');
    $this->assertGuest();
    $this->assertDatabaseMissing('users', ['email' => 'usuario@gmail.com']);
});

// TC-03: logout encerra sessão
it('destroys session on logout', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->post('/logout');

    $response->assertRedirect('/login');
    $this->assertGuest();
});
