<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Permission;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
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

    /**
     * Store a newly created role with permissions.
     */
    public function storeRole(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name'          => ['required', 'string', 'max:50', 'unique:roles,name'],
            'permissions'   => ['nullable', 'array'],
            'permissions.*' => ['string', 'exists:permissions,name'],
        ], [
            'name.required' => 'اسم الدور مطلوب',
            'name.unique'   => 'هذا الدور موجود بالفعل',
        ]);

        $roleName = strtolower(trim($validated['name']));
        $guard = 'sanctum';

        $role = Role::create([
            'name'       => $roleName,
            'guard_name' => $guard,
        ]);

        if (!empty($validated['permissions'])) {
            $role->syncPermissions($validated['permissions']);
        }

        return back()->with('success', 'تم إنشاء الدور بنجاح وإسناد الصلاحيات المحددة.');
    }

    /**
     * Update an existing role and its permissions.
     */
    public function updateRole(Request $request, Role $role): RedirectResponse
    {
        $validated = $request->validate([
            'name'          => ['required', 'string', 'max:50', Rule::unique('roles', 'name')->ignore($role->id)],
            'permissions'   => ['nullable', 'array'],
            'permissions.*' => ['string', 'exists:permissions,name'],
        ], [
            'name.required' => 'اسم الدور مطلوب',
            'name.unique'   => 'هذا الدور موجود بالفعل',
        ]);

        // Protect admin role name from rename, but allow permission sync
        if ($role->name !== 'admin') {
            $role->name = strtolower(trim($validated['name']));
            $role->save();
        }

        $permissions = $validated['permissions'] ?? [];
        $role->syncPermissions($permissions);

        return back()->with('success', "تم تحديث الدور '{$role->name}' وصلاحياته بنجاح.");
    }

    /**
     * Delete a custom role.
     */
    public function destroyRole(Role $role): RedirectResponse
    {
        if (in_array(strtolower($role->name), ['admin', 'user'])) {
            return back()->with('error', 'لا يمكن حذف الأدوار الأساسية للنظام (admin / user).');
        }

        if ($role->users()->count() > 0) {
            return back()->with('error', 'لا يمكن حذف هذا الدور لوجود مستخدمين مسندين إليه حالياً.');
        }

        $roleName = $role->name;
        $role->delete();

        return back()->with('success', "تم حذف الدور '{$roleName}' بنجاح.");
    }
}
