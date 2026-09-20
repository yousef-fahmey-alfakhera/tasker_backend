@extends('layouts.dashboard')

@section('title', __('dashboard.dashboard_title'))
@section('header_title', __('dashboard.header_title_overview'))

@section('content')
<div class="space-y-8">

    <!-- Top Date Range Filter Bar (Default: Yesterday to Tomorrow) -->
    <section class="bg-white dark:bg-slate-900 p-5 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm transition-all">
        <form action="{{ route('dashboard.index') }}" method="GET" id="filterForm" class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
            <div class="flex items-center gap-3">
                <div class="p-2 rounded-xl bg-brand-50 dark:bg-brand-900/30 text-brand-600 dark:text-brand-400">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                </div>
                <div>
                    <h2 class="text-sm font-extrabold text-slate-900 dark:text-white">{{ __('dashboard.filter_timeline_range') }}</h2>
                    <p class="text-xs text-slate-500 dark:text-slate-400">{{ __('dashboard.default_range_hint') }}</p>
                </div>
            </div>

            <div class="flex flex-wrap items-center gap-3">
                <div class="flex items-center gap-2">
                    <label for="fromInput" class="text-xs font-bold text-slate-600 dark:text-slate-400">{{ __('dashboard.from') }}</label>
                    <input type="date" name="from" id="fromInput" value="{{ $fromInput }}"
                        class="px-3 py-1.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-900 dark:text-white text-xs font-semibold focus:ring-2 focus:ring-brand-500 focus:outline-none">
                </div>

                <div class="flex items-center gap-2">
                    <label for="toInput" class="text-xs font-bold text-slate-600 dark:text-slate-400">{{ __('dashboard.to') }}</label>
                    <input type="date" name="to" id="toInput" value="{{ $toInput }}"
                        class="px-3 py-1.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-900 dark:text-white text-xs font-semibold focus:ring-2 focus:ring-brand-500 focus:outline-none">
                </div>

                <button type="submit" id="applyFilterBtn" class="px-4 py-2 rounded-xl bg-brand-600 hover:bg-brand-700 text-white font-bold text-xs shadow-md shadow-indigo-500/20 transition-all">
                    {{ __('dashboard.apply_filter') }}
                </button>

                <!-- Quick Presets -->
                <div class="hidden sm:flex items-center gap-1.5 border-l rtl:border-l-0 rtl:border-r border-slate-200 dark:border-slate-700 pl-3 rtl:pl-0 rtl:pr-3">
                    <button type="button" onclick="setPreset('default')" class="px-2.5 py-1.5 rounded-lg text-xs font-semibold bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 hover:bg-brand-50 dark:hover:bg-slate-700">
                        {{ __('dashboard.preset_default') }}
                    </button>
                    <button type="button" onclick="setPreset('today')" class="px-2.5 py-1.5 rounded-lg text-xs font-semibold bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 hover:bg-brand-50 dark:hover:bg-slate-700">
                        {{ __('dashboard.preset_today') }}
                    </button>
                    <button type="button" onclick="setPreset('week')" class="px-2.5 py-1.5 rounded-lg text-xs font-semibold bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 hover:bg-brand-50 dark:hover:bg-slate-700">
                        {{ __('dashboard.preset_week') }}
                    </button>
                    <button type="button" onclick="setPreset('month')" class="px-2.5 py-1.5 rounded-lg text-xs font-semibold bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 hover:bg-brand-50 dark:hover:bg-slate-700">
                        {{ __('dashboard.preset_month') }}
                    </button>
                </div>
            </div>
        </form>
    </section>

    <!-- Key Metric Statistics Cards -->
    <section class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
        <!-- Card 1: Total Tasks in Range -->
        <div class="bg-white dark:bg-slate-900 p-6 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm relative overflow-hidden group hover:border-brand-500/50 transition-all">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">{{ __('dashboard.tasks_in_range') }}</p>
                    <p class="text-3xl font-extrabold text-slate-900 dark:text-white mt-1">{{ $totalTasksInRange }}</p>
                </div>
                <div class="w-12 h-12 rounded-2xl bg-brand-50 dark:bg-brand-900/30 text-brand-600 dark:text-brand-400 flex items-center justify-center font-bold text-xl shadow-inner">
                    📋
                </div>
            </div>
            <div class="mt-4 flex items-center gap-2 text-xs font-semibold text-slate-500 dark:text-slate-400">
                <span class="text-emerald-600 dark:text-emerald-400 font-bold">{{ $allTimeTasksCount }} {{ __('dashboard.total_all_time') }}</span>
                <span>{{ __('dashboard.all_time_in_workspace') }}</span>
            </div>
        </div>

        <!-- Card 2: Completed Tasks & Rate -->
        <div class="bg-white dark:bg-slate-900 p-6 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm relative overflow-hidden group hover:border-emerald-500/50 transition-all">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">{{ __('dashboard.completed_tasks') }}</p>
                    <p class="text-3xl font-extrabold text-emerald-600 dark:text-emerald-400 mt-1">{{ $completedTasksCount }}</p>
                </div>
                <div class="w-12 h-12 rounded-2xl bg-emerald-50 dark:bg-emerald-900/30 text-emerald-600 dark:text-emerald-400 flex items-center justify-center font-bold text-xl shadow-inner">
                    ✅
                </div>
            </div>
            <div class="mt-4 flex items-center gap-2 text-xs font-semibold text-slate-500 dark:text-slate-400">
                <span>{{ __('dashboard.completion_rate') }}</span>
                <span class="font-bold text-emerald-600 dark:text-emerald-400">{{ $completionRate }}%</span>
            </div>
        </div>

        <!-- Card 3: In Progress & Pending Tasks -->
        <div class="bg-white dark:bg-slate-900 p-6 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm relative overflow-hidden group hover:border-amber-500/50 transition-all">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">{{ __('dashboard.working_pending') }}</p>
                    <div class="flex items-baseline gap-2 mt-1">
                        <span class="text-3xl font-extrabold text-amber-500">{{ $workingTasksCount }}</span>
                        <span class="text-xs text-slate-400 font-bold">{{ __('dashboard.working_stat') }}</span>
                        <span class="text-lg font-bold text-slate-400">/ {{ $pendingTasksCount }}</span>
                        <span class="text-xs text-slate-400 font-bold">{{ __('dashboard.pending_stat') }}</span>
                    </div>
                </div>
                <div class="w-12 h-12 rounded-2xl bg-amber-50 dark:bg-amber-900/30 text-amber-600 dark:text-amber-400 flex items-center justify-center font-bold text-xl shadow-inner">
                    ⚡
                </div>
            </div>
            <div class="mt-4 flex items-center gap-2 text-xs font-semibold text-slate-500 dark:text-slate-400">
                <span class="text-rose-500 font-bold">{{ $urgentHighCount }}</span>
                <span>{{ __('dashboard.urgent_high_priority') }}</span>
            </div>
        </div>

        <!-- Card 4: Workspaces & Avg Time -->
        <div class="bg-white dark:bg-slate-900 p-6 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm relative overflow-hidden group hover:border-indigo-500/50 transition-all">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">{{ __('dashboard.workspaces_projects') }}</p>
                    <p class="text-3xl font-extrabold text-slate-900 dark:text-white mt-1">{{ $totalWorkspaces }} <span class="text-lg font-semibold text-slate-400">/ {{ $totalProjects }}</span></p>
                </div>
                <div class="w-12 h-12 rounded-2xl bg-indigo-50 dark:bg-indigo-900/30 text-indigo-600 dark:text-indigo-400 flex items-center justify-center font-bold text-xl shadow-inner">
                    📁
                </div>
            </div>
            <div class="mt-4 flex items-center gap-2 text-xs font-semibold text-slate-500 dark:text-slate-400">
                <span>{{ __('dashboard.avg_resolution') }}</span>
                <span class="font-bold text-slate-700 dark:text-slate-300">{{ round($avgResolutionMinutes) }} {{ __('dashboard.mins') }}</span>
            </div>
        </div>
    </section>

    <!-- Charts Row 1: Tasks Activity & Priority Breakdown -->
    <section class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- 1. Tasks Volume Timeline Chart -->
        <div class="lg:col-span-2 bg-white dark:bg-slate-900 p-6 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h3 class="font-bold text-base text-slate-900 dark:text-white">{{ __('dashboard.tasks_activity_timeline') }}</h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400">{{ __('dashboard.timeline_subtitle') }}</p>
                </div>
                <span class="px-2.5 py-1 rounded-full text-[11px] font-bold bg-brand-50 text-brand-700 dark:bg-brand-900/40 dark:text-brand-300">
                    {{ __('dashboard.live_data') }}
                </span>
            </div>
            <div class="h-64 w-full">
                <canvas id="tasksTimelineChart"></canvas>
            </div>
        </div>

        <!-- 2. Priority Breakdown Doughnut Chart -->
        <div class="bg-white dark:bg-slate-900 p-6 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm flex flex-col justify-between">
            <div>
                <h3 class="font-bold text-base text-slate-900 dark:text-white">{{ __('dashboard.priority_distribution') }}</h3>
                <p class="text-xs text-slate-500 dark:text-slate-400 mb-4">{{ __('dashboard.priority_subtitle') }}</p>
                <div class="h-56 w-full flex items-center justify-center">
                    <canvas id="priorityChart"></canvas>
                </div>
            </div>
            <div class="pt-4 border-t border-slate-100 dark:border-slate-800 flex justify-around text-center text-xs">
                <div>
                    <span class="block font-extrabold text-rose-500">{{ $priorityCounts[0] ?? 0 }}</span>
                    <span class="text-slate-400">{{ __('dashboard.urgent') }}</span>
                </div>
                <div>
                    <span class="block font-extrabold text-orange-500">{{ $priorityCounts[1] ?? 0 }}</span>
                    <span class="text-slate-400">{{ __('dashboard.high') }}</span>
                </div>
                <div>
                    <span class="block font-extrabold text-blue-500">{{ $priorityCounts[2] ?? 0 }}</span>
                    <span class="text-slate-400">{{ __('dashboard.normal') }}</span>
                </div>
                <div>
                    <span class="block font-extrabold text-slate-500">{{ $priorityCounts[3] ?? 0 }}</span>
                    <span class="text-slate-400">{{ __('dashboard.low') }}</span>
                </div>
            </div>
        </div>
    </section>

    <!-- Charts Row 2: The Two Required Active Users Charts with User Types -->
    <section class="grid grid-cols-1 lg:grid-cols-2 gap-6">

        <!-- Chart 2: Active Users (Fixed By) Chart -->
        <div class="bg-white dark:bg-slate-900 p-6 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h3 class="font-bold text-base text-slate-900 dark:text-white flex items-center gap-2">
                        <span>{{ __('dashboard.active_solvers_title') }}</span>
                        <span class="px-2 py-0.5 rounded text-[10px] font-extrabold bg-emerald-100 text-emerald-700 dark:bg-emerald-950 dark:text-emerald-300 uppercase">{{ __('dashboard.fixed_by_badge') }}</span>
                    </h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400">{{ __('dashboard.active_solvers_subtitle') }}</p>
                </div>
            </div>
            <div class="h-64 w-full">
                <canvas id="activeFixersChart"></canvas>
            </div>
        </div>

        <!-- Chart 3: Active Complaints / Requesters Users (Created By) Chart -->
        <div class="bg-white dark:bg-slate-900 p-6 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h3 class="font-bold text-base text-slate-900 dark:text-white flex items-center gap-2">
                        <span>{{ __('dashboard.active_creators_title') }}</span>
                        <span class="px-2 py-0.5 rounded text-[10px] font-extrabold bg-indigo-100 text-indigo-700 dark:bg-indigo-950 dark:text-indigo-300 uppercase">{{ __('dashboard.created_by_badge') }}</span>
                    </h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400">{{ __('dashboard.active_creators_subtitle') }}</p>
                </div>
            </div>
            <div class="h-64 w-full">
                <canvas id="activeCreatorsChart"></canvas>
            </div>
        </div>
    </section>

    <!-- Workspaces Section: Newest 5 Cards in Each Workspace -->
    <section class="space-y-6">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-lg font-extrabold text-slate-900 dark:text-white">{{ __('dashboard.workspace_boards_title') }}</h2>
                <p class="text-xs text-slate-500 dark:text-slate-400">{{ __('dashboard.workspace_boards_subtitle') }}</p>
            </div>
            <a href="{{ route('dashboard.workspaces') }}" class="text-xs font-bold text-brand-600 dark:text-brand-400 hover:underline">
                {{ __('dashboard.view_all_workspaces') }} &rarr;
            </a>
        </div>

        <div class="grid grid-cols-1 gap-6">
            @forelse($workspaces as $ws)
            <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm overflow-hidden">
                <!-- Workspace Header -->
                <div class="px-6 py-4 bg-slate-50 dark:bg-slate-800/60 border-b border-slate-200 dark:border-slate-800 flex flex-wrap items-center justify-between gap-3">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-lg bg-brand-600 text-white font-bold flex items-center justify-center text-xs shadow-sm">
                            {{ strtoupper(substr($ws->name, 0, 2)) }}
                        </div>
                        <div>
                            <h3 class="font-extrabold text-sm text-slate-900 dark:text-white">{{ $ws->name }}</h3>
                            <span class="text-xs text-slate-500 dark:text-slate-400">{{ __('dashboard.project_label') }} <strong>{{ $ws->project?->name ?? 'General' }}</strong></span>
                        </div>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="px-2.5 py-1 rounded-full text-[11px] font-bold bg-slate-200 dark:bg-slate-700 text-slate-700 dark:text-slate-300">
                            {{ $ws->tasks_count }} {{ __('dashboard.total_tasks_count') }}
                        </span>
                        <a href="{{ route('dashboard.tasks', ['workspace_id' => $ws->id]) }}" class="px-3 py-1 rounded-lg text-xs font-bold bg-brand-50 text-brand-600 dark:bg-brand-900/30 dark:text-brand-300 hover:bg-brand-100 transition-colors">
                            {{ __('dashboard.manage_btn') }} &rarr;
                        </a>
                    </div>
                </div>

                <!-- 5 Newest Task Cards -->
                <div class="p-6">
                    @if($ws->newest_tasks && $ws->newest_tasks->count() > 0)
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-5 gap-4">
                        @foreach($ws->newest_tasks as $task)
                        <div class="p-4 rounded-xl border border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-800/40 flex flex-col justify-between hover:shadow-md hover:border-brand-500/50 transition-all group">
                            <div>
                                <!-- Badges -->
                                <div class="flex items-center justify-between gap-1 mb-2">
                                    <!-- Status Stage Badge -->
                                    @php
                                        $stageColor = match($task->status?->stage) {
                                            'completed' => 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-300',
                                            'working'   => 'bg-amber-100 text-amber-800 dark:bg-amber-950 dark:text-amber-300',
                                            default     => 'bg-slate-200 text-slate-800 dark:bg-slate-700 dark:text-slate-300'
                                        };
                                        $priorityColor = match($task->priority) {
                                            'Urgent' => 'bg-rose-100 text-rose-800 dark:bg-rose-950 dark:text-rose-300',
                                            'High'   => 'bg-orange-100 text-orange-800 dark:bg-orange-950 dark:text-orange-300',
                                            'Normal' => 'bg-blue-100 text-blue-800 dark:bg-blue-950 dark:text-blue-300',
                                            default  => 'bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-400'
                                        };
                                    @endphp
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold {{ $stageColor }}">
                                        {{ $task->status?->name ?? 'Todo' }}
                                    </span>
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold {{ $priorityColor }}">
                                        {{ $task->priority ?? __('dashboard.none') }}
                                    </span>
                                </div>

                                <!-- Title -->
                                <h4 class="text-xs font-bold text-slate-900 dark:text-white line-clamp-2 group-hover:text-brand-600 dark:group-hover:text-brand-400 transition-colors">
                                    {{ $task->title }}
                                </h4>

                                <!-- Task Type -->
                                @if($task->taskType)
                                <div class="mt-2">
                                    <span class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded text-[10px] font-semibold bg-indigo-50 text-indigo-700 dark:bg-indigo-950/60 dark:text-indigo-300 border border-indigo-200 dark:border-indigo-800">
                                        🏷️ {{ $task->taskType->name }} ({{ $task->taskType->type }})
                                    </span>
                                </div>
                                @endif
                            </div>

                            <!-- Footer Details: Creator & Fixed By -->
                            <div class="mt-4 pt-3 border-t border-slate-200/80 dark:border-slate-800/80 text-[11px] text-slate-500 dark:text-slate-400 space-y-1">
                                <div class="flex items-center justify-between">
                                    <span>{{ __('dashboard.created_by_label') }}</span>
                                    <span class="font-semibold text-slate-800 dark:text-slate-200 truncate max-w-[90px]">{{ $task->creator?->name ?? 'System' }}</span>
                                </div>
                                <div class="flex items-center justify-between">
                                    <span>{{ __('dashboard.fixed_by_label') }}</span>
                                    <span class="font-semibold text-slate-800 dark:text-slate-200 truncate max-w-[90px]">{{ $task->fixedBy?->name ?? __('dashboard.unassigned') }}</span>
                                </div>
                                <div class="text-[10px] text-slate-400 pt-1 text-right rtl:text-left">
                                    {{ $task->created_at?->diffForHumans() }}
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                    @else
                    <div class="text-center py-8 text-slate-400 text-xs font-medium">
                        {{ __('dashboard.no_tasks_in_workspace') }}
                    </div>
                    @endif
                </div>
            </div>
            @empty
            <div class="bg-white dark:bg-slate-900 p-8 rounded-2xl text-center border border-slate-200 dark:border-slate-800">
                <p class="text-sm font-semibold text-slate-500">{{ __('dashboard.no_workspaces_available') }}</p>
            </div>
            @endforelse
        </div>
    </section>

</div>
@endsection

@push('scripts')
<script>
    // Quick presets handler
    function setPreset(type) {
        const now = new Date();
        let from, to;

        if (type === 'default') {
            // Yesterday to tomorrow
            const y = new Date(now);
            y.setDate(now.getDate() - 1);
            const tm = new Date(now);
            tm.setDate(now.getDate() + 1);
            from = y.toISOString().split('T')[0];
            to = tm.toISOString().split('T')[0];
        } else if (type === 'today') {
            from = now.toISOString().split('T')[0];
            to = now.toISOString().split('T')[0];
        } else if (type === 'week') {
            const first = new Date(now.setDate(now.getDate() - now.getDay()));
            const last = new Date(now.setDate(now.getDate() - now.getDay() + 6));
            from = first.toISOString().split('T')[0];
            to = last.toISOString().split('T')[0];
        } else if (type === 'month') {
            const first = new Date(now.getFullYear(), now.getMonth(), 1);
            const last = new Date(now.getFullYear(), now.getMonth() + 1, 0);
            from = first.toISOString().split('T')[0];
            to = last.toISOString().split('T')[0];
        }

        document.getElementById('fromInput').value = from;
        document.getElementById('toInput').value = to;
        document.getElementById('filterForm').submit();
    }

    // Chart.js Theme Helpers
    const isDark = document.documentElement.classList.contains('dark');
    const gridColor = isDark ? 'rgba(51, 65, 85, 0.4)' : 'rgba(226, 232, 240, 0.8)';
    const textColor = isDark ? '#94a3b8' : '#64748b';

    // 1. Tasks Activity Timeline Chart
    const ctxTimeline = document.getElementById('tasksTimelineChart').getContext('2d');
    new Chart(ctxTimeline, {
        type: 'bar',
        data: {
            labels: @json($timelineDates),
            datasets: [
                {
                    label: @json(__('dashboard.completed')),
                    data: @json($timelineCompleted),
                    backgroundColor: '#10b981',
                    borderRadius: 6
                },
                {
                    label: @json(__('dashboard.working')),
                    data: @json($timelineWorking),
                    backgroundColor: '#f59e0b',
                    borderRadius: 6
                },
                {
                    label: @json(__('dashboard.pending')),
                    data: @json($timelinePending),
                    backgroundColor: '#6366f1',
                    borderRadius: 6
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { position: 'top', labels: { color: textColor, font: { family: 'Plus Jakarta Sans', size: 11, weight: 'bold' } } }
            },
            scales: {
                x: { stacked: true, grid: { display: false }, ticks: { color: textColor } },
                y: { stacked: true, grid: { color: gridColor }, ticks: { color: textColor, stepSize: 1 } }
            }
        }
    });

    // 2. Priority Doughnut Chart
    const ctxPriority = document.getElementById('priorityChart').getContext('2d');
    new Chart(ctxPriority, {
        type: 'doughnut',
        data: {
            labels: @json($priorityLabels),
            datasets: [{
                data: @json($priorityCounts),
                backgroundColor: ['#f43f5e', '#f97316', '#3b82f6', '#94a3b8', '#cbd5e1'],
                borderWidth: 0,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            cutout: '70%',
            plugins: {
                legend: { display: false }
            }
        }
    });

    // 3. Active Fixers Chart (Fixed By users with User Types)
    const ctxFixers = document.getElementById('activeFixersChart').getContext('2d');
    new Chart(ctxFixers, {
        type: 'bar',
        data: {
            labels: @json($fixerLabels),
            datasets: [{
                label: @json(__('dashboard.tasks_resolved')),
                data: @json($fixerCounts),
                backgroundColor: '#10b981',
                borderRadius: 8
            }]
        },
        options: {
            indexAxis: 'y',
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false }
            },
            scales: {
                x: { grid: { color: gridColor }, ticks: { color: textColor, stepSize: 1 } },
                y: { grid: { display: false }, ticks: { color: textColor, font: { weight: 'bold' } } }
            }
        }
    });

    // 4. Active Creators Chart (Created By users with User Types)
    const ctxCreators = document.getElementById('activeCreatorsChart').getContext('2d');
    new Chart(ctxCreators, {
        type: 'bar',
        data: {
            labels: @json($creatorLabels),
            datasets: [{
                label: @json(__('dashboard.tasks_complaints_logged')),
                data: @json($creatorCounts),
                backgroundColor: '#6366f1',
                borderRadius: 8
            }]
        },
        options: {
            indexAxis: 'y',
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false }
            },
            scales: {
                x: { grid: { color: gridColor }, ticks: { color: textColor, stepSize: 1 } },
                y: { grid: { display: false }, ticks: { color: textColor, font: { weight: 'bold' } } }
            }
        }
    });
</script>
@endpush
