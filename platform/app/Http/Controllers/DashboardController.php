<?php

namespace App\Http\Controllers;

use App\Enums\EngagementStatus;
use App\Enums\FindingStatus;
use App\Enums\Severity;
use App\Models\ActivityLog;
use App\Models\Engagement;
use App\Models\Finding;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __invoke(Request $request): Response
    {
        $user = $request->user();

        // Aggregates are cached per user for a minute (Redis in production).
        $stats = Cache::remember("dashboard:{$user->id}", 60, function () use ($user) {
            $engagementIds = Engagement::query()->visibleTo($user)->pluck('id');
            $findings = Finding::query()->whereIn('engagement_id', $engagementIds);

            $bySeverity = (clone $findings)
                ->where('status', '!=', FindingStatus::Remediated->value)
                ->selectRaw('severity, count(*) as total')
                ->groupBy('severity')
                ->pluck('total', 'severity');

            $byStatus = Engagement::query()->whereIn('id', $engagementIds)
                ->selectRaw('status, count(*) as total')
                ->groupBy('status')
                ->pluck('total', 'status');

            $total = (clone $findings)->count();

            return [
                'engagements' => $engagementIds->count(),
                'active' => (int) ($byStatus[EngagementStatus::InProgress->value] ?? 0),
                'findings' => $total,
                'remediated' => (clone $findings)->where('status', FindingStatus::Remediated->value)->count(),
                'averageCvss' => round((float) (clone $findings)->avg('cvss_score'), 1),
                'severity' => collect(Severity::ordered())->map(fn (Severity $s) => [
                    'value' => $s->value,
                    'label' => $s->label(),
                    'color' => $s->color(),
                    'total' => (int) ($bySeverity[$s->value] ?? 0),
                ])->all(),
                'status' => collect(EngagementStatus::cases())->map(fn (EngagementStatus $s) => [
                    'value' => $s->value,
                    'label' => $s->label(),
                    'total' => (int) ($byStatus[$s->value] ?? 0),
                ])->all(),
            ];
        });

        $critical = Finding::query()
            ->whereIn('engagement_id', Engagement::query()->visibleTo($user)->select('id'))
            ->where('status', '!=', FindingStatus::Remediated->value)
            ->with('engagement:id,code,title')
            ->orderByDesc('cvss_score')
            ->limit(6)
            ->get(['id', 'engagement_id', 'number', 'title', 'cvss_score', 'severity', 'status']);

        $engagements = Engagement::query()->visibleTo($user)
            ->with('client:id,name')
            ->withCount('findings')
            ->latest('starts_on')
            ->limit(5)
            ->get();

        return Inertia::render('Dashboard', [
            'stats' => $stats,
            'critical' => $critical,
            'engagements' => $engagements,
            // Admins see the whole trail, pentesters only their own actions.
            'activity' => $user->isStaff()
                ? ActivityLog::query()
                    ->when(! $user->isAdmin(), fn ($q) => $q->where('user_id', $user->id))
                    ->with('user:id,name')
                    ->latest('created_at')
                    ->limit(8)
                    ->get()
                : [],
        ]);
    }
}
