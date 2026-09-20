@extends('layouts.dashboard')

@section('title', __('dashboard.workspaces_title'))
@section('header_title', __('dashboard.workspaces_header'))

@section('content')
<div class="space-y-8">
    <!-- Projects Section -->
    <div class="space-y-4">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-base font-extrabold text-slate-900 dark:text-white">{{ __('dashboard.active_projects') }}</h2>
                <p class="text-xs text-slate-500 dark:text-slate-400">{{ __('dashboard.projects_sub') }}</p>
            </div>
            <span class="px-3 py-1 rounded-xl text-xs font-bold bg-brand-50 text-brand-600 dark:bg-brand-900/30 dark:text-brand-300">
                {{ $projects->count() }} {{ __('dashboard.projects_count_badge') }}
            </span>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
            @forelse($projects as $proj)
            <div class="bg-white dark:bg-slate-900 p-6 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm flex flex-col justify-between hover:border-brand-500/50 transition-all">
                <div>
                    <div class="flex items-center justify-between mb-3">
                        <span class="w-8 h-8 rounded-lg bg-indigo-50 dark:bg-indigo-950/80 text-indigo-600 dark:text-indigo-400 flex items-center justify-center font-bold text-xs">
                            📂
                        </span>
                        <span class="text-xs font-semibold text-slate-400">{{ __('dashboard.by_creator') }} {{ $proj->creator?->name ?? 'Admin' }}</span>
                    </div>
                    <h3 class="font-bold text-sm text-slate-900 dark:text-white">{{ $proj->name }}</h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-1 line-clamp-2">
                        {{ $proj->description ?? __('dashboard.project_default_desc') }}
                    </p>
                </div>

                <div class="mt-5 pt-3 border-t border-slate-100 dark:border-slate-800 flex items-center justify-between text-xs font-semibold text-slate-500">
                    <div>{{ __('dashboard.workspaces_stat') }} <span class="font-extrabold text-slate-800 dark:text-slate-200">{{ $proj->workspaces_count }}</span></div>
                    <div>{{ __('dashboard.tasks_stat') }} <span class="font-extrabold text-brand-600 dark:text-brand-400">{{ $proj->tasks_count }}</span></div>
                </div>
            </div>
            @empty
            <div class="col-span-3 p-8 text-center bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 text-xs text-slate-400">
                {{ __('dashboard.no_projects_found') }}
            </div>
            @endforelse
        </div>
    </div>

    <!-- Workspaces Section -->
    <div class="space-y-4">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-base font-extrabold text-slate-900 dark:text-white">{{ __('dashboard.assigned_workspaces') }}</h2>
                <p class="text-xs text-slate-500 dark:text-slate-400">{{ __('dashboard.workspaces_sub') }}</p>
            </div>
            <span class="px-3 py-1 rounded-xl text-xs font-bold bg-brand-50 text-brand-600 dark:bg-brand-900/30 dark:text-brand-300">
                {{ $workspaces->total() }} {{ __('dashboard.workspaces_count_badge') }}
            </span>
        </div>

        <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left rtl:text-right text-sm">
                    <thead class="bg-slate-50 dark:bg-slate-800/60 border-b border-slate-200 dark:border-slate-800 text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">
                        <tr>
                            <th class="px-6 py-4">{{ __('dashboard.workspace_col') }}</th>
                            <th class="px-6 py-4">{{ __('dashboard.parent_project_col') }}</th>
                            <th class="px-6 py-4">{{ __('dashboard.assigned_members_col') }}</th>
                            <th class="px-6 py-4">{{ __('dashboard.tasks_col') }}</th>
                            <th class="px-6 py-4">{{ __('dashboard.created_by_col') }}</th>
                            <th class="px-6 py-4 text-right rtl:text-left">{{ __('dashboard.actions_col') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800/60">
                        @forelse($workspaces as $ws)
                        <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/30 transition-colors">
                            <td class="px-6 py-4 font-bold text-slate-900 dark:text-white">
                                {{ $ws->name }}
                            </td>
                            <td class="px-6 py-4">
                                <span class="px-2.5 py-1 rounded-lg text-xs font-semibold bg-indigo-50 text-indigo-700 dark:bg-indigo-950/60 dark:text-indigo-300">
                                    {{ $ws->project?->name ?? __('dashboard.none') }}
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center -space-x-2 overflow-hidden">
                                    @foreach($ws->users->take(4) as $m)
                                    <div class="w-7 h-7 rounded-full bg-slate-200 dark:bg-slate-700 border-2 border-white dark:border-slate-900 flex items-center justify-center text-[10px] font-bold text-slate-700 dark:text-slate-300" title="{{ $m->name }} ({{ $m->pivot->role }})">
                                        {{ strtoupper(substr($m->name, 0, 1)) }}
                                    </div>
                                    @endforeach
                                    @if($ws->users->count() > 4)
                                    <div class="w-7 h-7 rounded-full bg-slate-100 dark:bg-slate-800 border-2 border-white dark:border-slate-900 flex items-center justify-center text-[10px] font-bold text-slate-400">
                                        +{{ $ws->users->count() - 4 }}
                                    </div>
                                    @endif
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <span class="font-extrabold text-brand-600 dark:text-brand-400">{{ $ws->tasks_count }}</span>
                            </td>
                            <td class="px-6 py-4 text-xs text-slate-500">
                                {{ $ws->creator?->name ?? 'System' }}
                            </td>
                            <td class="px-6 py-4 text-right rtl:text-left">
                                <a href="{{ route('dashboard.tasks', ['workspace_id' => $ws->id]) }}" class="px-3 py-1.5 rounded-xl bg-brand-50 hover:bg-brand-100 text-brand-600 dark:bg-brand-900/30 dark:text-brand-300 text-xs font-bold transition-colors">
                                    {{ __('dashboard.view_tasks_btn') }} &rarr;
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="px-6 py-8 text-center text-slate-400 text-xs font-medium">{{ __('dashboard.no_workspaces_found') }}</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="p-4 border-t border-slate-200 dark:border-slate-800">
                {{ $workspaces->links() }}
            </div>
        </div>
    </div>
</div>
@endsection
