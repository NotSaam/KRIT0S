<?php

use App\Models\Engagement;
use App\Models\Finding;
use App\Models\User;

beforeEach(function () {
    $this->lead = User::factory()->create();
    $this->engagement = Engagement::factory()->create(['lead_id' => $this->lead->id]);
});

function findingPayload(array $overrides = []): array
{
    return [
        'title' => 'Inyección SQL en login',
        'cvss_vector' => 'CVSS:3.1/AV:N/AC:L/PR:N/UI:N/S:U/C:H/I:H/A:H',
        'status' => 'open',
        'affected_assets' => 'https://app.example.test/login',
        'description' => 'Descripción',
        'impact' => 'Impacto',
        'remediation' => 'Remediación',
        ...$overrides,
    ];
}

it('derives score and severity from the vector, ignoring client-sent values', function () {
    $this->actingAs($this->lead)
        ->post(route('engagements.findings.store', $this->engagement), findingPayload([
            'cvss_score' => 1.0,
            'severity' => 'low',
        ]))
        ->assertRedirect(route('engagements.show', $this->engagement));

    $finding = Finding::sole();
    expect($finding->cvss_score)->toBe(9.8)
        ->and($finding->severity->value)->toBe('critical')
        ->and($finding->reference)->toBe('F-01')
        ->and($finding->author_id)->toBe($this->lead->id);
});

it('numbers findings per engagement', function () {
    Finding::factory()->for($this->engagement)->count(2)->create();

    $this->actingAs($this->lead)->post(route('engagements.findings.store', $this->engagement), findingPayload());

    expect($this->engagement->findings()->max('number'))->toBe(3);
});

it('rejects an invalid CVSS vector', function () {
    $this->actingAs($this->lead)
        ->post(route('engagements.findings.store', $this->engagement), findingPayload(['cvss_vector' => 'CVSS:3.1/AV:N']))
        ->assertSessionHasErrors('cvss_vector');

    expect(Finding::count())->toBe(0);
});

it('blocks pentesters outside the engagement', function () {
    $this->actingAs(User::factory()->create())
        ->post(route('engagements.findings.store', $this->engagement), findingPayload())
        ->assertForbidden();
});

it('scopes finding routes to their engagement', function () {
    $foreign = Finding::factory()->create();

    $this->actingAs(User::factory()->admin()->create())
        ->get(route('engagements.findings.edit', [$this->engagement, $foreign]))
        ->assertNotFound();
});

it('updates a finding and recalculates severity', function () {
    $finding = Finding::factory()->for($this->engagement)->create();

    $this->actingAs($this->lead)->put(
        route('engagements.findings.update', [$this->engagement, $finding]),
        findingPayload(['cvss_vector' => 'CVSS:3.1/AV:L/AC:H/PR:H/UI:R/S:U/C:L/I:N/A:N', 'status' => 'remediated']),
    )->assertRedirect();

    expect($finding->fresh())
        ->cvss_score->toBe(1.8)
        ->severity->value->toBe('low')
        ->status->value->toBe('remediated');
});
