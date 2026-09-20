<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\Task;
use App\Models\TaskStatus;
use App\Models\TaskType;
use App\Models\Workspace;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class TaskManagementController extends Controller
{
    /**
     * Display tasks list, task types, and task statuses.
     */
    public function index(Request $request): View
    {
        $taskTypes = TaskType::withCount('tasks')->get();
        $taskStatuses = TaskStatus::withCount('tasks')->orderBy('order')->get();
        $workspaces = Workspace::all();
        $projects = Project::all();

        $query = Task::with(['status', 'taskType', 'creator', 'fixedBy', 'workspace.project'])
            ->latest();

        if ($request->filled('workspace_id')) {
            $query->where('workspace_id', $request->input('workspace_id'));
        }

        if ($request->filled('status_id')) {
            $query->where('status_id', $request->input('status_id'));
        }

        if ($request->filled('task_type_id')) {
            $query->where('task_type_id', $request->input('task_type_id'));
        }

        if ($request->filled('priority')) {
            $query->where('priority', $request->input('priority'));
        }

        $tasks = $query->paginate(15)->withQueryString();

        return view('dashboard.tasks.index', compact(
            'tasks',
            'taskTypes',
            'taskStatuses',
            'workspaces',
            'projects'
        ));
    }

    /**
     * Store new task type.
     */
    public function storeType(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'type' => ['required', 'string', Rule::in(['network', 'device', 'focus', 'other'])],
        ]);

        TaskType::create($validated);

        return back()->with('success', 'Task type created successfully.');
    }

    /**
     * Store new task status.
     */
    public function storeStatus(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name'  => ['required', 'string', 'max:255'],
            'stage' => ['required', 'string', Rule::in(['pending', 'working', 'completed'])],
            'order' => ['required', 'integer', 'min:1'],
        ]);

        TaskStatus::create($validated);

        return back()->with('success', 'Task status created successfully.');
    }
}
