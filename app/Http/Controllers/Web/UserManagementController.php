<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserType;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class UserManagementController extends Controller
{
    /**
     * Display users list with user types and responsibilities.
     */
    public function index(): View
    {
        $users = User::with(['userType', 'roles'])
            ->withCount(['createdTasks', 'fixedTasks'])
            ->latest()
            ->paginate(15);

        return view('dashboard.users.index', compact('users'));
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
