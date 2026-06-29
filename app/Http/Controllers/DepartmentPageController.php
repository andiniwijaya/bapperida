<?php

namespace App\Http\Controllers;

use App\Models\Department;
use Illuminate\View\View;

class DepartmentPageController extends Controller
{
    public function index(): View
    {
        $this->authorize('viewAny', Department::class);

        return view('departments.index');
    }

    public function create(): View
    {
        $this->authorize('create', Department::class);

        return view('departments.create');
    }

    public function edit(Department $department): View
    {
        $this->authorize('update', $department);

        return view('departments.edit', [
            'departmentId' => $department->id,
        ]);
    }
}
