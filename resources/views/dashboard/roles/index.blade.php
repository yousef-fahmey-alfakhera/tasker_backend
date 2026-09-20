@extends('layouts.dashboard')

@section('title', __('dashboard.roles_title'))
@section('header_title', __('dashboard.roles_header'))

@section('content')
<div class="space-y-8">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h2 class="text-lg font-extrabold text-slate-900 dark:text-white">{{ __('dashboard.roles_matrix_title') }}</h2>
            <p class="text-xs text-slate-500 dark:text-slate-400">{{ __('dashboard.roles_matrix_sub') }}</p>
        </div>
        <div class="flex items-center gap-2">
            <span class="px-3 py-1 rounded-xl text-xs font-bold bg-brand-50 text-brand-600 dark:bg-brand-900/30 dark:text-brand-300">
                {{ $permissions->count() }} {{ __('dashboard.total_permissions') }}
            </span>
        </div>
    </div>

    <!-- Roles Overview Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
        @foreach($roles as $role)
        <div class="bg-white dark:bg-slate-900 p-6 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between mb-3">
                    <span class="px-3 py-1 rounded-full text-xs font-extrabold {{ $role->name === 'admin' ? 'bg-purple-100 text-purple-700 dark:bg-purple-950 dark:text-purple-300' : 'bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-300' }}">
                        ROLE: {{ strtoupper($role->name) }}
                    </span>
                    <span class="text-xs font-bold text-slate-400">{{ __('dashboard.guard_label') }} {{ $role->guard_name }}</span>
                </div>
                <h3 class="text-base font-bold text-slate-900 dark:text-white">
                    {{ $role->name === 'admin' ? __('dashboard.super_admin') : __('dashboard.standard_user') }}
                </h3>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">
                    {{ $role->name === 'admin' ? __('dashboard.admin_desc') : __('dashboard.user_desc') }}
                </p>
            </div>
            <div class="mt-6 pt-4 border-t border-slate-100 dark:border-slate-800 flex items-center justify-between text-xs font-semibold">
                <span class="text-slate-500">{{ __('dashboard.granted_privileges') }}</span>
                <span class="font-extrabold text-brand-600 dark:text-brand-400">{{ $role->permissions->count() }} {{ __('dashboard.permissions_word') }}</span>
            </div>
        </div>
        @endforeach
    </div>

    <!-- Permissions Matrix by Resource -->
    <div class="space-y-4">
        <h3 class="text-base font-extrabold text-slate-900 dark:text-white">{{ __('dashboard.permissions_by_module') }}</h3>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
            @foreach($groupedPermissions as $resource => $perms)
            <div class="bg-white dark:bg-slate-900 p-5 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm">
                <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-3 mb-3">
                    <h4 class="font-bold text-sm text-slate-900 dark:text-white capitalize">
                        📦 {{ str_replace('_', ' ', $resource) }}
                    </h4>
                    <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400">
                        {{ $perms->count() }} {{ __('dashboard.actions_word') }}
                    </span>
                </div>

                <ul class="space-y-2.5">
                    @foreach($perms as $p)
                    <li class="p-2.5 rounded-xl bg-slate-50 dark:bg-slate-800/50 border border-slate-100 dark:border-slate-800/80 flex items-center justify-between gap-2">
                        <div>
                            <span class="block font-mono text-xs font-bold text-slate-800 dark:text-slate-200">{{ $p->name }}</span>
                            <span class="block text-[11px] text-slate-500 dark:text-slate-400 font-medium">{{ $p->name_ar ?? 'صلاحية عامة' }}</span>
                        </div>
                        <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                    </li>
                    @endforeach
                </ul>
            </div>
            @endforeach
        </div>
    </div>
</div>
@endsection
