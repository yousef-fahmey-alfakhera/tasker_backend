<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Permission;
use Illuminate\View\View;
use Spatie\Permission\Models\Role;

class RolePermissionController extends Controller
{
    /**
     * Display roles and permissions grouped by module.
     */
    public function index(): View
    {
        $roles = Role::with('permissions')->get();
        $permissions = Permission::all();

        // Group permissions by resource (e.g. tasks, projects, etc.)
        $groupedPermissions = $permissions->groupBy(function ($permission) {
            $parts = explode('_', $permission->name, 2);

            return $parts[1] ?? 'general';
        });

        return view('dashboard.roles.index', compact('roles', 'permissions', 'groupedPermissions'));
    }
}
