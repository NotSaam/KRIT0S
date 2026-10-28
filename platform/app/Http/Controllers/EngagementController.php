<?php

namespace App\Http\Controllers;

use App\Enums\EngagementStatus;
use App\Enums\EngagementType;
use App\Enums\FindingStatus;
use App\Enums\Role;
use App\Http\Requests\EngagementRequest;
use App\Models\ActivityLog;
use App\Models\Client;
use App\Models\Engagement;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class EngagementController extends Controller
{
    public function index(Request $request): Response
    {
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
            'status' => ['nullable', 'string'],
        ]);

        $engagements = Engagement::query()
            ->visibleTo($request->user())
            ->with(['client:id,name', 'lead:id,name'])
            ->withCount('findings')
            ->when($filters['search'] ?? null, fn ($q, $search) => $q->where(fn ($q) => $q
                ->whereLike('title', "%{$search}%")
                ->orWhereLike('code', "%{$search}%")))
            ->when($filters['status'] ?? null, fn ($q, $status) => $q->where('status', $status))
            ->latest('starts_on')
            ->paginate(12)
            ->withQueryString();

        return Inertia::render('engagements/Index', [
            'engagements' => $engagements,
            'filters' => $filters,
            'statuses' => EngagementStatus::options(),
            'can' => ['create' => $request->user()->can('create', Engagement::class)],
        ]);
    }

    public function create(): Response
    {
        Gate::authorize('create', Engagement::class);

        return Inertia::render('engagements/Form', [
            ...$this->formOptions(),
            'engagement' => null,
            'nextCode' => Engagement::nextCode(),
        ]);
    }

    public function store(EngagementRequest $request): RedirectResponse
    {
        Gate::authorize('create', Engagement::class);

        $engagement = Engagement::create([
            ...Arr::except($request->validated(), 'team'),
            'code' => Engagement::nextCode(),
        ]);
        $engagement->team()->sync($request->validated('team', []));

        ActivityLog::record('engagement.created', "Proyecto {$engagement->code} creado", $engagement);

        return to_route('engagements.show', $engagement)->with('success', 'Proyecto creado.');
    }

    public function show(Request $request, Engagement $engagement): Response
    {
        Gate::authorize('view', $engagement);

        $engagement->load(['client', 'lead:id,name,email', 'team:id,name,email', 'reports.author:id,name']);

        $findings = $engagement->findings()
            ->orderByDesc('cvss_score')
            ->orderBy('number')
            ->get();

        $user = $request->user();

        return Inertia::render('engagements/Show', [
            'engagement' => $engagement,
            'findings' => $findings,
            'breakdown' => $engagement->severityBreakdown(),
            'findingStatuses' => FindingStatus::options(),
            'can' => [
                'update' => $user->can('update', $engagement),
                'delete' => $user->can('delete', $engagement),
                'publish' => $user->can('publish', $engagement),
                'generateReport' => $user->can('generateReport', $engagement),
            ],
        ]);
    }

    public function edit(Engagement $engagement): Response
    {
        Gate::authorize('update', $engagement);

        return Inertia::render('engagements/Form', [
            ...$this->formOptions(),
            'engagement' => [
                ...$engagement->toArray(),
                'starts_on' => $engagement->starts_on->toDateString(),
                'ends_on' => $engagement->ends_on->toDateString(),
                'team' => $engagement->team()->pluck('users.id'),
            ],
            'nextCode' => $engagement->code,
        ]);
    }

    public function update(EngagementRequest $request, Engagement $engagement): RedirectResponse
    {
        Gate::authorize('update', $engagement);

        $engagement->update(Arr::except($request->validated(), 'team'));
        $engagement->team()->sync($request->validated('team', []));

        ActivityLog::record('engagement.updated', "Proyecto {$engagement->code} actualizado", $engagement, [
            'changed' => array_keys($engagement->getChanges()),
        ]);

        return to_route('engagements.show', $engagement)->with('success', 'Proyecto actualizado.');
    }

    public function destroy(Engagement $engagement): RedirectResponse
    {
        Gate::authorize('delete', $engagement);

        ActivityLog::record('engagement.deleted', "Proyecto {$engagement->code} eliminado", null, [
            'code' => $engagement->code,
        ]);
        $engagement->delete();

        return to_route('engagements.index')->with('success', 'Proyecto eliminado.');
    }

    /**
     * Publishing hands the verdict to the client portal; unpublishing pulls it back.
     */
    public function publish(Engagement $engagement): RedirectResponse
    {
        Gate::authorize('publish', $engagement);

        $published = ! $engagement->isPublished();
        $engagement->update([
            'published_at' => $published ? now() : null,
            'status' => $published ? EngagementStatus::Delivered : $engagement->status,
        ]);

        ActivityLog::record(
            $published ? 'engagement.published' : 'engagement.unpublished',
            ($published ? 'Informe publicado al cliente: ' : 'Informe retirado del portal: ').$engagement->code,
            $engagement,
        );

        return back()->with('success', $published ? 'Publicado en el portal del cliente.' : 'Retirado del portal del cliente.');
    }

    /**
     * @return array<string, mixed>
     */
    private function formOptions(): array
    {
        return [
            'clients' => Client::query()->orderBy('name')->get(['id', 'name']),
            'staff' => User::query()
                ->whereIn('role', [Role::Admin, Role::Pentester])
                ->orderBy('name')
                ->get(['id', 'name', 'role']),
            'types' => EngagementType::options(),
            'statuses' => EngagementStatus::options(),
        ];
    }
}
