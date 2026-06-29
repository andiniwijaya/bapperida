<?php

namespace App\Http\Controllers;

use App\Models\Dashboard;
use App\Models\Department;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Web presentation for role-specific dashboards.
 *
 * Business rules:
 * - Each role renders a dedicated Blade view (no shared widget hiding).
 * - Data is loaded client-side from Dashboard API; Blade provides layout only.
 *
 * Related modules: DashboardPolicy, DashboardService (API).
 */
class DashboardPageController extends Controller
{
    /**
     * Render the dashboard view matching the authenticated user's role.
     */
    public function index(Request $request): View
    {
        $user = $request->user();

        $this->authorize(match ($user->role) {
            'superadmin' => 'viewSuperAdmin',
            'admin' => 'viewAdmin',
            'staff' => 'viewStaff',
            default => 'view',
        }, Dashboard::class);

        $meta = match ($user->role) {
            'superadmin' => [
                'title' => 'Beranda Super Admin',
                'description' => 'Ringkasan sistem, pengguna, bidang, dan aktivitas surat secara keseluruhan.',
                'showDepartmentFilter' => true,
            ],
            'admin' => [
                'title' => 'Beranda Admin',
                'description' => 'Ringkasan operasi surat dan aktivitas bidang dalam sistem.',
                'showDepartmentFilter' => true,
            ],
            'staff' => [
                'title' => 'Beranda Staff',
                'description' => 'Ringkasan surat dan aktivitas yang Anda kelola.',
                'showDepartmentFilter' => false,
            ],
            default => abort(403),
        };

        $departments = in_array($user->role, ['superadmin', 'admin'], true)
            ? Department::query()->active()->orderBy('name')->get()
            : collect();

        return view('dashboard.index', [
            'role' => $user->role,
            'departments' => $departments,
            ...$meta,
        ]);
    }
}
