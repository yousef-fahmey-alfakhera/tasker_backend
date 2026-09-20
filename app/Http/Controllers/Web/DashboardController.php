<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\Task;
use App\Models\TaskStatus;
use App\Models\TaskType;
use App\Models\User;
use App\Models\Workspace;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Dashboard Overview with Statistics, 3 Main Charts, Filter, and Newest 5 Cards per Workspace.
     */
    public function index(Request $request): View
    {
        // Default filter: from yesterday to tomorrow
        $defaultFrom = Carbon::yesterday()->format('Y-m-d');
        $defaultTo = Carbon::tomorrow()->format('Y-m-d');

        $fromInput = $request->input('from', $defaultFrom);
        $toInput = $request->input('to', $defaultTo);

        try {
            $from = Carbon::parse($fromInput)->startOfDay();
        } catch (\Exception) {
            $from = Carbon::yesterday()->startOfDay();
            $fromInput = $defaultFrom;
        }

        try {
            $to = Carbon::parse($toInput)->endOfDay();
        } catch (\Exception) {
            $to = Carbon::tomorrow()->endOfDay();
            $toInput = $defaultTo;
        }

        // Base query in date range
        $tasksInRange = Task::with(['status', 'taskType', 'creator', 'fixedBy', 'workspace.project'])
            ->whereBetween('created_at', [$from, $to]);

        // Key Metric KPIs
        $totalTasksInRange = (clone $tasksInRange)->count();
        $allTimeTasksCount = Task::count();

        $completedTasksCount = (clone $tasksInRange)->whereHas('status', fn ($q) => $q->where('stage', 'completed'))->count();
        $workingTasksCount = (clone $tasksInRange)->whereHas('status', fn ($q) => $q->where('stage', 'working'))->count();
        $pendingTasksCount = (clone $tasksInRange)->whereHas('status', fn ($q) => $q->where('stage', 'pending'))->count();

        $urgentHighCount = (clone $tasksInRange)->whereIn('priority', ['Urgent', 'High'])->count();
        $avgResolutionMinutes = (clone $tasksInRange)->whereNotNull('actual_minutes')->avg('actual_minutes') ?? 0;

        $completionRate = $totalTasksInRange > 0 ? round(($completedTasksCount / $totalTasksInRange) * 100, 1) : 0;

        $totalWorkspaces = Workspace::count();
        $totalProjects = Project::count();
        $totalUsers = User::count();

        // 1. Task Volume & Timeline Chart (Day by Day)
        $timelineDates = [];
        $timelinePending = [];
        $timelineWorking = [];
        $timelineCompleted = [];

        $currentDay = (clone $from)->copy();
        while ($currentDay->lte($to)) {
            $dayStr = $currentDay->format('Y-m-d');
            $dayStart = (clone $currentDay)->startOfDay();
            $dayEnd = (clone $currentDay)->endOfDay();

            $timelineDates[] = $currentDay->format('M d');
            $timelinePending[] = Task::whereBetween('created_at', [$dayStart, $dayEnd])
                ->whereHas('status', fn ($q) => $q->where('stage', 'pending'))->count();
            $timelineWorking[] = Task::whereBetween('created_at', [$dayStart, $dayEnd])
                ->whereHas('status', fn ($q) => $q->where('stage', 'working'))->count();
            $timelineCompleted[] = Task::whereBetween('created_at', [$dayStart, $dayEnd])
                ->whereHas('status', fn ($q) => $q->where('stage', 'completed'))->count();

            $currentDay->addDay();
        }

        // 2. Active Users Chart (Fixed By): Users with a UserType and their fixed_by count
        $activeFixers = User::has('userType')
            ->with('userType')
            ->withCount(['fixedTasks' => function ($q) use ($from, $to) {
                $q->whereBetween('created_at', [$from, $to]);
            }])
            ->orderByDesc('fixed_tasks_count')
            ->limit(8)
            ->get();

        // If in range they are 0, fallback to all time counts so chart looks rich
        if ($activeFixers->sum('fixed_tasks_count') === 0) {
            $activeFixers = User::has('userType')
                ->with('userType')
                ->withCount('fixedTasks')
                ->orderByDesc('fixed_tasks_count')
                ->limit(8)
                ->get();
        }

        $fixerLabels = [];
        $fixerCounts = [];
        $fixerTypes = [];
        foreach ($activeFixers as $fixer) {
            $typeLabel = $fixer->userType?->type ? strtoupper($fixer->userType->type) : 'GENERAL';
            $fixerLabels[] = $fixer->name . " ({$typeLabel})";
            $fixerCounts[] = $fixer->fixed_tasks_count;
            $fixerTypes[] = $typeLabel;
        }

        // 3. Active Complaints / Requesters Users Chart (Created By): Users with a UserType and their created_by count
        $activeCreators = User::has('userType')
            ->with('userType')
            ->withCount(['createdTasks' => function ($q) use ($from, $to) {
                $q->whereBetween('created_at', [$from, $to]);
            }])
            ->orderByDesc('created_tasks_count')
            ->limit(8)
            ->get();

        if ($activeCreators->sum('created_tasks_count') === 0) {
            $activeCreators = User::has('userType')
                ->with('userType')
                ->withCount('createdTasks')
                ->orderByDesc('created_tasks_count')
                ->limit(8)
                ->get();
        }

        $creatorLabels = [];
        $creatorCounts = [];
        $creatorTypes = [];
        foreach ($activeCreators as $creator) {
            $typeLabel = $creator->userType?->type ? strtoupper($creator->userType->type) : 'USER';
            $creatorLabels[] = $creator->name . " ({$typeLabel})";
            $creatorCounts[] = $creator->created_tasks_count;
            $creatorTypes[] = $typeLabel;
        }

        // 4. Priority Breakdown Chart
        $priorityLabels = ['Urgent', 'High', 'Normal', 'Low', 'None'];
        $priorityCounts = [];
        foreach ($priorityLabels as $p) {
            $priorityCounts[] = (clone $tasksInRange)->where('priority', $p)->count();
        }
        if (array_sum($priorityCounts) === 0) {
            foreach ($priorityLabels as $index => $p) {
                $priorityCounts[$index] = Task::where('priority', $p)->count();
            }
        }

        // 5. Task Types Breakdown
        $taskTypesList = TaskType::all();
        $taskTypeLabels = [];
        $taskTypeCounts = [];
        foreach ($taskTypesList as $tType) {
            $taskTypeLabels[] = $tType->name . ' (' . $tType->type . ')';
            $cnt = (clone $tasksInRange)->where('task_type_id', $tType->id)->count();
            if ($cnt === 0) {
                $cnt = Task::where('task_type_id', $tType->id)->count();
            }
            $taskTypeCounts[] = $cnt;
        }

        // 6. Newest 5 Cards in Each Workspace
        $workspaces = Workspace::with(['project', 'users', 'creator'])
            ->withCount('tasks')
            ->get()
            ->map(function ($workspace) use ($from, $to) {
                $recent = Task::with(['status', 'taskType', 'creator', 'fixedBy'])
                    ->where('workspace_id', $workspace->id)
                    ->whereBetween('created_at', [$from, $to])
                    ->latest()
                    ->limit(5)
                    ->get();

                // If none in strict date range, fetch the newest 5 overall
                if ($recent->isEmpty()) {
                    $recent = Task::with(['status', 'taskType', 'creator', 'fixedBy'])
                        ->where('workspace_id', $workspace->id)
                        ->latest()
                        ->limit(5)
                        ->get();
                }

                $workspace->newest_tasks = $recent;

                return $workspace;
            });

        return view('dashboard.index', compact(
            'fromInput',
            'toInput',
            'totalTasksInRange',
            'allTimeTasksCount',
            'completedTasksCount',
            'workingTasksCount',
            'pendingTasksCount',
            'urgentHighCount',
            'avgResolutionMinutes',
            'completionRate',
            'totalWorkspaces',
            'totalProjects',
            'totalUsers',
            'timelineDates',
            'timelinePending',
            'timelineWorking',
            'timelineCompleted',
            'fixerLabels',
            'fixerCounts',
            'fixerTypes',
            'creatorLabels',
            'creatorCounts',
            'creatorTypes',
            'priorityLabels',
            'priorityCounts',
            'taskTypeLabels',
            'taskTypeCounts',
            'workspaces'
        ));
    }
}
