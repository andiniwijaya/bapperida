<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use Illuminate\View\View;

class ActivityLogPageController extends Controller
{
    public function index(): View
    {
        $this->authorize('viewAny', ActivityLog::class);

        return view('activity-logs.index');
    }

    public function show(ActivityLog $activityLog): View
    {
        $this->authorize('view', $activityLog);

        return view('activity-logs.show', [
            'activityLogId' => $activityLog->id,
        ]);
    }
}
