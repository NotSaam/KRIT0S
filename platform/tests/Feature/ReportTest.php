<?php

use App\Jobs\GenerateReport;
use App\Models\Client;
use App\Models\Engagement;
use App\Models\Finding;
use App\Models\ReportVersion;
use App\Models\User;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    Storage::fake('local');
    $this->client = Client::factory()->create();
    $this->lead = User::factory()->create();
    $this->engagement = Engagement::factory()->for($this->client)->create(['lead_id' => $this->lead->id]);
    Finding::factory()->for($this->engagement)->count(3)->create();
});

it('queues a new report version', function () {
    Queue::fake();

    $this->actingAs($this->lead)->post(route('engagements.reports.store', $this->engagement))->assertRedirect();
    $this->actingAs($this->lead)->post(route('engagements.reports.store', $this->engagement));

    expect($this->engagement->reports()->pluck('version')->all())->toBe([2, 1]);
    Queue::assertPushed(GenerateReport::class, 2);
});

it('renders a hashed PDF and lets the client download it once published', function () {
    $this->actingAs($this->lead)->post(route('engagements.reports.store', $this->engagement));

    $report = ReportVersion::sole();
    expect($report->status)->toBe(ReportVersion::READY)
        ->and($report->findings_count)->toBe(3)
        ->and($report->sha256)->toHaveLength(64);

    $pdf = Storage::disk('local')->get($report->path);
    expect(substr($pdf, 0, 4))->toBe('%PDF')
        ->and(hash('sha256', $pdf))->toBe($report->sha256);

    $clientUser = User::factory()->client($this->client)->create();
    $url = route('engagements.reports.download', [$this->engagement, $report]);

    $this->actingAs($clientUser)->get($url)->assertForbidden();

    $this->engagement->update(['published_at' => now()]);
    $this->actingAs($clientUser)->get($url)
        ->assertOk()
        ->assertHeader('content-type', 'application/pdf');

    $this->assertDatabaseHas('activity_logs', ['action' => 'report.downloaded', 'user_id' => $clientUser->id]);
});

it('does not let clients generate reports', function () {
    $this->engagement->update(['published_at' => now()]);

    $this->actingAs(User::factory()->client($this->client)->create())
        ->post(route('engagements.reports.store', $this->engagement))
        ->assertForbidden();
});
