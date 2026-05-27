<?php

use App\Models\RideRequest;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

// TC-V01: tela de login acessível sem autenticação
it('login page is accessible without authentication', function () {
    $response = $this->get('/login');

    $response->assertStatus(200);
    $response->assertSee('Entrar com Google');
    $response->assertSee('@ufla.br');
});

// TC-V02: rotas protegidas redirecionam para login sem autenticação
it('protected routes redirect to login when unauthenticated', function () {
    foreach (['/dashboard', '/rides/create'] as $route) {
        $this->get($route)->assertRedirect('/login');
    }
});

// TC-V03: dashboard do passageiro exibe suas solicitações
it('passenger dashboard shows their ride requests', function () {
    $passenger = User::factory()->passenger()->create();
    RideRequest::factory()->count(2)->create(['passenger_id' => $passenger->id]);

    $response = $this->actingAs($passenger)->get('/dashboard');

    $response->assertStatus(200);
    $response->assertSee('Solicitar Carona');
    $response->assertSee('Minhas Solicitações');
});

// TC-V04: dashboard do passageiro vazio exibe mensagem de incentivo
it('passenger dashboard with no requests shows empty state', function () {
    $passenger = User::factory()->passenger()->create();

    $response = $this->actingAs($passenger)->get('/dashboard');

    $response->assertStatus(200);
    $response->assertSee('Nenhuma solicitação ainda');
});

// TC-V05: dashboard do motorista exibe solicitações pendentes e seção de caronas
it('driver dashboard shows pending requests and rides sections', function () {
    $driver = User::factory()->driver()->create();

    $response = $this->actingAs($driver)->get('/dashboard');

    $response->assertStatus(200);
    $response->assertSee('Solicitações Pendentes');
    $response->assertSee('Minhas Caronas');
});

// TC-V06: motorista vê botão de editar veículo quando tem veículo cadastrado
it('driver dashboard shows vehicle edit link when vehicle exists', function () {
    $driver  = User::factory()->driver()->create();
    $vehicle = Vehicle::factory()->create(['user_id' => $driver->id]);

    $response = $this->actingAs($driver)->get('/dashboard');

    $response->assertStatus(200);
    $response->assertSee($vehicle->plate);
    $response->assertSee('Editar');
});

// TC-V07: formulário de solicitar carona acessível ao passageiro
it('ride create form is accessible to passengers', function () {
    $passenger = User::factory()->passenger()->create();

    $response = $this->actingAs($passenger)->get('/rides/create');

    $response->assertStatus(200);
    $response->assertSee('Solicitar Carona');
    $response->assertSee('Origem');
    $response->assertSee('Destino');
    $response->assertSee('Data e Hora');
});

// TC-V08: formulário de editar veículo exibe dados do veículo
it('vehicle edit form shows vehicle data', function () {
    $driver  = User::factory()->driver()->create();
    $vehicle = Vehicle::factory()->create(['user_id' => $driver->id]);

    $response = $this->actingAs($driver)->get("/vehicles/{$vehicle->id}/edit");

    $response->assertStatus(200);
    $response->assertSee($vehicle->model);
    $response->assertSee($vehicle->plate);
});

// TC-V09: motorista não pode editar veículo de outro usuário
it('driver cannot access another drivers vehicle edit form', function () {
    $driver1 = User::factory()->driver()->create();
    $driver2 = User::factory()->driver()->create();
    $vehicle = Vehicle::factory()->create(['user_id' => $driver2->id]);

    $this->actingAs($driver1)->get("/vehicles/{$vehicle->id}/edit")->assertStatus(403);
});
