<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Http\Request;
use Spatie\Activitylog\Models\Activity;

class AuthLogController extends Controller
{
    public function index(Request $request): Response
    {
        $logs = Activity::with('causer')
            ->where('log_name', 'auth')
            ->when($request->search, function ($q, $search) {
                $q->where('description', 'like', "%{$search}%")
                  ->orWhere('properties', 'like', "%{$search}%");
            })
            ->when($request->type, function ($q, $type) {
                $q->where('properties->event_type', $type);
            })
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return Inertia::render('Admin/AuthLogs/Index', [
            'logs' => $logs,
            'filters' => $request->only(['search', 'type']),
        ]);
    }
}
