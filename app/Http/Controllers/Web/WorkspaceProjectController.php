<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\Workspace;
use Illuminate\View\View;

class WorkspaceProjectController extends Controller
{
    /**
     * Display workspaces and projects overview.
     */
    public function index(): View
    {
        $projects = Project::with(['creator', 'workspaces'])
            ->withCount(['workspaces', 'tasks'])
            ->latest()
            ->get();

        $workspaces = Workspace::with(['project', 'creator', 'users'])
            ->withCount('tasks')
            ->latest()
            ->paginate(10);

        return view('dashboard.workspaces.index', compact('projects', 'workspaces'));
    }
}
