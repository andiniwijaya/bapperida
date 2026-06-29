<x-layouts::app :title="$title">
    @include('dashboard.partials.shell', [
        'title' => $title,
        'description' => $description,
        'role' => $role,
        'showFilters' => true,
        'showDepartmentFilter' => $showDepartmentFilter,
        'departments' => $departments,
    ])
</x-layouts::app>
