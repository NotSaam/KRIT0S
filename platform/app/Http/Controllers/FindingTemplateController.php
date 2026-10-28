<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\FindingTemplate;
use App\Rules\CvssVector;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class FindingTemplateController extends Controller
{
    public function index(Request $request): Response
    {
        Gate::authorize('viewAny', FindingTemplate::class);

        $search = $request->string('search')->trim()->toString();

        return Inertia::render('templates/Index', [
            'templates' => FindingTemplate::query()
                ->withCount('findings')
                ->when($search, fn ($q) => $q->whereLike('title', "%{$search}%")->orWhereLike('category', "%{$search}%"))
                ->orderBy('category')
                ->orderBy('title')
                ->get(),
            'filters' => ['search' => $search],
            'can' => ['delete' => $request->user()->isAdmin()],
        ]);
    }

    public function create(): Response
    {
        Gate::authorize('create', FindingTemplate::class);

        return Inertia::render('templates/Form', ['template' => null]);
    }

    public function store(Request $request): RedirectResponse
    {
        Gate::authorize('create', FindingTemplate::class);

        $template = FindingTemplate::create($this->validated($request));
        ActivityLog::record('template.created', "Plantilla «{$template->title}» creada", $template);

        return to_route('templates.index')->with('success', 'Plantilla creada.');
    }

    public function edit(FindingTemplate $template): Response
    {
        Gate::authorize('update', $template);

        return Inertia::render('templates/Form', ['template' => $template]);
    }

    public function update(Request $request, FindingTemplate $template): RedirectResponse
    {
        Gate::authorize('update', $template);

        $template->update($this->validated($request));
        ActivityLog::record('template.updated', "Plantilla «{$template->title}» actualizada", $template);

        return to_route('templates.index')->with('success', 'Plantilla actualizada.');
    }

    public function destroy(FindingTemplate $template): RedirectResponse
    {
        Gate::authorize('delete', $template);

        ActivityLog::record('template.deleted', "Plantilla «{$template->title}» eliminada");
        $template->delete();

        return to_route('templates.index')->with('success', 'Plantilla eliminada.');
    }

    /**
     * @return array<string, mixed>
     */
    private function validated(Request $request): array
    {
        return $request->validate([
            'title' => ['required', 'string', 'max:200'],
            'category' => ['required', 'string', 'max:80'],
            'cvss_vector' => ['required', 'string', new CvssVector],
            'description' => ['required', 'string', 'max:20000'],
            'impact' => ['required', 'string', 'max:10000'],
            'remediation' => ['required', 'string', 'max:10000'],
            'references' => ['nullable', 'string', 'max:3000'],
        ]);
    }
}
