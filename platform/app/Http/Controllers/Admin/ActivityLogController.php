<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ActivityLogController extends Controller
{
    public function index(Request $request): Response
    {
        $action = $request->string('action')->toString();

        return Inertia::render('admin/Activity', [
            'logs' => ActivityLog::query()
                ->with('user:id,name,email')
                ->when($action, fn ($q) => $q->where('action', 'like', "{$action}%"))
                ->latest('created_at')
                ->latest('id')
                ->paginate(25)
                ->withQueryString(),
            'filters' => ['action' => $action],
        ]);
    }
}
