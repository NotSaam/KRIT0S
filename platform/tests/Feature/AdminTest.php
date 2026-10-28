<?php

use App\Enums\Role;
use App\Models\Client;
use App\Models\User;

beforeEach(function () {
    $this->admin = User::factory()->admin()->create();
});

it('provisions client users tied to a client', function () {
    $client = Client::factory()->create();

    $this->actingAs($this->admin)->post(route('admin.users.store'), [
        'name' => 'CISO Cliente',
        'email' => 'ciso@cliente.test',
        'password' => 'una-clave-larga-123',
        'role' => 'client',
        'client_id' => $client->id,
    ])->assertSessionHasNoErrors();

    $user = User::where('email', 'ciso@cliente.test')->sole();
    expect($user->role)->toBe(Role::Client)->and($user->client_id)->toBe($client->id);
});

it('requires a client for client users and drops it for staff', function () {
    $this->actingAs($this->admin)->post(route('admin.users.store'), [
        'name' => 'Sin cliente', 'email' => 'x@test.test', 'password' => 'una-clave-larga-123', 'role' => 'client',
    ])->assertSessionHasErrors('client_id');

    $this->actingAs($this->admin)->post(route('admin.users.store'), [
        'name' => 'Pentester', 'email' => 'p@test.test', 'password' => 'una-clave-larga-123', 'role' => 'pentester',
        'client_id' => Client::factory()->create()->id,
    ]);

    expect(User::where('email', 'p@test.test')->value('client_id'))->toBeNull();
});

it('never demotes the last admin', function () {
    $this->actingAs($this->admin)
        ->put(route('admin.users.update', $this->admin), ['role' => 'pentester'])
        ->assertSessionHasErrors('role');

    expect($this->admin->fresh()->isAdmin())->toBeTrue();
});

it('records an audit trail', function () {
    $this->actingAs($this->admin)->post(route('admin.clients.store'), ['name' => 'Cliente Auditado']);

    $this->actingAs($this->admin)
        ->get(route('admin.activity.index'))
        ->assertInertia(fn ($page) => $page->where('logs.data.0.action', 'client.created'));
});

it('has no public registration', function () {
    $this->get('/register')->assertNotFound();
});
