<?php

namespace App\Http\Controllers;

use App\Jobs\GenerateReport;
use App\Models\ActivityLog;
use App\Models\Engagement;
use App\Models\ReportVersion;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    /**
     * Queue a new immutable report version. The PDF is rendered by a worker.
     */
    public function store(Request $request, Engagement $engagement): RedirectResponse
    {
        Gate::authorize('generateReport', $engagement);

        $report = $engagement->reports()->create([
            'version' => (int) $engagement->reports()->max('version') + 1,
            'generated_by' => $request->user()->id,
            'status' => ReportVersion::QUEUED,
        ]);

        GenerateReport::dispatch($report);

        return back()->with('success', "Informe v{$report->version} en cola de generación.");
    }

    public function download(Engagement $engagement, ReportVersion $report): StreamedResponse
    {
        Gate::authorize('downloadReport', $engagement);
        abort_unless($report->engagement_id === $engagement->id && $report->status === ReportVersion::READY, 404);

        $report->setRelation('engagement', $engagement);
        ActivityLog::record('report.downloaded', "Informe v{$report->version} de {$engagement->code} descargado", $engagement);

        return Storage::disk('local')->download($report->path, $report->filename(), [
            'Content-Type' => 'application/pdf',
        ]);
    }
}
