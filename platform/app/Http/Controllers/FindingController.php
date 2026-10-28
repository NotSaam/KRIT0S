<?php

namespace App\Http\Controllers;

use App\Enums\FindingStatus;
use App\Http\Requests\FindingRequest;
use App\Models\ActivityLog;
use App\Models\Engagement;
use App\Models\Finding;
use App\Models\FindingTemplate;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class FindingController extends Controller
{
    public function create(Request $request, Engagement $engagement): Response
    {
        Gate::authorize('update', $engagement);

        $template = $request->integer('template')
            ? FindingTemplate::find($request->integer('template'))
            : null;

        return Inertia::render('findings/Form', [
            ...$this->formOptions($engagement),
            'finding' => $template ? [
                'template_id' => $template->id,
                'title' => $template->title,
                'cvss_vector' => $template->cvss_vector,
                'description' => $template->description,
                'impact' => $template->impact,
                'remediation' => $template->remediation,
                'references' => $template->references,
            ] : null,
        ]);
    }

    public function store(FindingRequest $request, Engagement $engagement): RedirectResponse
    {
        Gate::authorize('update', $engagement);

        $finding = $engagement->findings()->create([
            ...$request->validated(),
            'author_id' => $request->user()->id,
        ]);

        ActivityLog::record('finding.created', "{$finding->reference} «{$finding->title}» añadido a {$engagement->code}", $finding, [
            'severity' => $finding->severity->value,
            'cvss' => $finding->cvss_score,
        ]);

        return to_route('engagements.show', $engagement)->with('success', "Hallazgo {$finding->reference} registrado.");
    }

    public function edit(Engagement $engagement, Finding $finding): Response
    {
        Gate::authorize('update', $engagement);

        return Inertia::render('findings/Form', [
            ...$this->formOptions($engagement),
            'finding' => $finding,
        ]);
    }

    public function update(FindingRequest $request, Engagement $engagement, Finding $finding): RedirectResponse
    {
        Gate::authorize('update', $engagement);

        $finding->update($request->validated());

        ActivityLog::record('finding.updated', "{$finding->reference} actualizado en {$engagement->code}", $finding, [
            'changed' => array_keys($finding->getChanges()),
        ]);

        return to_route('engagements.show', $engagement)->with('success', "Hallazgo {$finding->reference} actualizado.");
    }

    public function destroy(Engagement $engagement, Finding $finding): RedirectResponse
    {
        Gate::authorize('update', $engagement);

        ActivityLog::record('finding.deleted', "{$finding->reference} eliminado de {$engagement->code}", $engagement, [
            'title' => $finding->title,
        ]);
        $finding->delete();

        return to_route('engagements.show', $engagement)->with('success', 'Hallazgo eliminado.');
    }

    /**
     * @return array<string, mixed>
     */
    private function formOptions(Engagement $engagement): array
    {
        return [
            'engagement' => $engagement->only('id', 'code', 'title'),
            'statuses' => FindingStatus::options(),
            'templates' => FindingTemplate::query()->orderBy('category')->orderBy('title')->get(['id', 'title', 'category', 'cvss_vector']),
        ];
    }
}
