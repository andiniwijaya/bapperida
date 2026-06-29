<?php

namespace App\Http\Controllers;

use App\Models\SystemSetting;
use Illuminate\View\View;

class SystemSettingPageController extends Controller
{
    public function index(): View
    {
        $this->authorize('viewAny', SystemSetting::class);

        return view('system-settings.index');
    }
}
