<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\User;
use App\Models\UserType;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class UserManagementController extends Controller
{
    /**
     * Display users list with user types, project, and soft delete filter.
     */
    public function index(Request $request): View
    {
        $isTrashed = (bool) $request->boolean('trashed');

        $query = $isTrashed ? User::onlyTrashed() : User::query();

        $users = $query->with(['userType', 'roles', 'project'])
            ->withCount(['createdTasks', 'fixedTasks'])
            ->latest('id')
            ->paginate(15)
            ->withQueryString();

        $activeCount = User::count();
        $trashedCount = User::onlyTrashed()->count();
        $projects = Project::all();

        return view('dashboard.users.index', compact('users', 'isTrashed', 'activeCount', 'trashedCount', 'projects'));
    }

    /**
     * Store new user.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name'       => ['required', 'string', 'max:255'],
            'email'      => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password'   => ['required', 'string', 'min:8'],
            'code'       => ['nullable', 'string', 'max:50', 'unique:users,code'],
            'project_id' => ['required', 'exists:projects,id'],
            'role'       => ['nullable', 'string', 'exists:roles,name'],
        ], [
            'project_id.required' => 'برجاء اختيار القسم',
            'project_id.exists'   => 'برجاء اختيار القسم',
        ]);

        $role = $validated['role'] ?? null;
        unset($validated['role']);

        $validated['password'] = Hash::make($validated['password']);
        if (isset($validated['code']) && empty($validated['code'])) {
            $validated['code'] = null;
        }

        $user = User::create($validated);

        if ($role) {
            $user->assignRole($role);
        }

        return back()->with('success', __('dashboard.user_created_success') ?: 'User created successfully.');
    }

    /**
     * Soft delete user.
     */
    public function destroy(User $user): RedirectResponse
    {
        $user->delete();

        return back()->with('success', __('dashboard.user_deleted_success') ?: 'User deleted successfully.');
    }

    /**
     * Restore soft-deleted user.
     */
    public function restore(int|string $id): RedirectResponse
    {
        $user = User::onlyTrashed()->findOrFail($id);
        $user->restore();

        return back()->with('success', __('dashboard.user_restored_success') ?: 'User restored successfully.');
    }

    /**
     * Update user type and responsibilities.
     */
    public function updateType(Request $request, User $user): RedirectResponse
    {
        $validated = $request->validate([
            'type'        => ['required', 'string', 'max:50'],
            'respnsapity' => ['nullable', 'string'], // comma-separated or array
        ]);

        $responsibilities = [];
        if (! empty($validated['respnsapity'])) {
            $responsibilities = array_values(array_filter(array_map('trim', explode(',', $validated['respnsapity']))));
        }

        UserType::updateOrCreate(
            ['user_id' => $user->id],
            [
                'type'        => $validated['type'],
                'respnsapity' => $responsibilities,
            ]
        );

        return back()->with('success', 'User type and responsibilities updated successfully.');
    }
}
