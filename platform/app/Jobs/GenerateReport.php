<?php

namespace App\Jobs;

use App\Enums\Severity;
use App\Models\ActivityLog;
use App\Models\ReportVersion;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Storage;
use Throwable;

/**
 * Renders an engagement's report to PDF off the request cycle and stores it
 * as an immutable, hashed version.
 */
class GenerateReport implements ShouldQueue
{
    use Queueable;

    public int $tries = 2;

    public function __construct(public ReportVersion $report) {}

    public function handle(): void
    {
        $engagement = $this->report->engagement->load(['client', 'lead', 'team']);

        $findings = $engagement->findings()
            ->orderByDesc('cvss_score')
            ->orderBy('number')
            ->get();

        $pdf = Pdf::loadView('reports.engagement', [
            'engagement' => $engagement,
            'findings' => $findings,
            'breakdown' => $engagement->severityBreakdown(),
            'severities' => Severity::ordered(),
            'version' => $this->report->version,
            'generatedAt' => now(),
        ])->setPaper('a4');

        $binary = $pdf->output();
        $path = "reports/{$engagement->code}/v{$this->report->version}.pdf";

        Storage::disk('local')->put($path, $binary);

        $this->report->update([
            'status' => ReportVersion::READY,
            'path' => $path,
            'sha256' => hash('sha256', $binary),
            'size' => strlen($binary),
            'findings_count' => $findings->count(),
        ]);

        ActivityLog::create([
            'user_id' => $this->report->generated_by,
            'action' => 'report.generated',
            'subject_type' => $engagement->getMorphClass(),
            'subject_id' => $engagement->id,
            'description' => "Informe v{$this->report->version} generado para {$engagement->code}",
            'properties' => ['sha256' => $this->report->sha256],
        ]);
    }

    public function failed(Throwable $e): void
    {
        $this->report->update(['status' => ReportVersion::FAILED]);
    }
}
