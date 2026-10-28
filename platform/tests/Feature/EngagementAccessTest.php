<?php

use App\Models\Client;
use App\Models\Engagement;
use App\Models\User;

beforeEach(function () {
    $this->client = Client::factory()->create();
    $this->lead = User::factory()->create();
    $this->engagement = Engagement::factory()->for($this->client)->create(['lead_id' => $this->lead->id]);
});

it('lets admins see every engagement', function () {
    $this->actingAs(User::factory()->admin()->create())
        ->get(route('engagements.show', $this->engagement))
        ->assertOk();
});

it('lets team members see the engagement and hides it from other pentesters', function () {
    $member = User::factory()->create();
    $this->engagement->team()->attach($member);

    $this->actingAs($member)->get(route('engagements.show', $this->engagement))->assertOk();
    $this->actingAs(User::factory()->create())->get(route('engagements.show', $this->engagement))->assertForbidden();
});

it('only shows clients their own published reports', function () {
    $clientUser = User::factory()->client($this->client)->create();

    $this->actingAs($clientUser)->get(route('engagements.show', $this->engagement))->assertForbidden();

    $this->engagement->update(['published_at' => now()]);
    $this->actingAs($clientUser)->get(route('engagements.show', $this->engagement))->assertOk();

    $other = Engagement::factory()->published()->create();
    $this->actingAs($clientUser)->get(route('engagements.show', $other))->assertForbidden();
});

it('scopes the index to visible engagements', function () {
    Engagement::factory()->count(2)->create();

    $this->actingAs($this->lead)
        ->get(route('engagements.index'))
        ->assertInertia(fn ($page) => $page
            ->component('engagements/Index')
            ->has('engagements.data', 1)
            ->where('engagements.data.0.id', $this->engagement->id));
});

it('prevents clients from editing or creating', function () {
    $clientUser = User::factory()->client($this->client)->create();
    $this->engagement->update(['published_at' => now()]);

    $this->actingAs($clientUser)->get(route('engagements.create'))->assertForbidden();
    $this->actingAs($clientUser)->get(route('engagements.edit', $this->engagement))->assertForbidden();
    $this->actingAs($clientUser)->get(route('templates.index'))->assertForbidden();
});

it('allows only the lead or an admin to publish', function () {
    $member = User::factory()->create();
    $this->engagement->team()->attach($member);

    $this->actingAs($member)->post(route('engagements.publish', $this->engagement))->assertForbidden();

    $this->actingAs($this->lead)->post(route('engagements.publish', $this->engagement))->assertRedirect();
    expect($this->engagement->fresh()->isPublished())->toBeTrue();
    $this->assertDatabaseHas('activity_logs', ['action' => 'engagement.published']);
});

it('keeps admin screens for admins', function () {
    $this->actingAs($this->lead)->get(route('admin.users.index'))->assertForbidden();
    $this->actingAs(User::factory()->admin()->create())->get(route('admin.users.index'))->assertOk();
});

it('creates engagements with a sequential code', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)->post(route('engagements.store'), [
        'client_id' => $this->client->id,
        'title' => 'Pentest API pagos',
        'type' => 'api',
        'status' => 'planning',
        'scope' => 'https://api.example.test',
        'starts_on' => '2026-11-01',
        'ends_on' => '2026-11-05',
        'lead_id' => $this->lead->id,
        'team' => [],
    ])->assertRedirect();

    expect(Engagement::latest('id')->first()->code)->toStartWith('KRT-'.now()->year.'-');
});
