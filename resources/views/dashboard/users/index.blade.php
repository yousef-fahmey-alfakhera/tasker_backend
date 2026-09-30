@extends('layouts.dashboard')

@section('title', __('dashboard.users_title'))
@section('header_title', __('dashboard.users_header'))

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h2 class="text-lg font-extrabold text-slate-900 dark:text-white">{{ $isTrashed ? __('dashboard.deleted_users') : __('dashboard.all_platform_users') }}</h2>
            <p class="text-xs text-slate-500 dark:text-slate-400">{{ __('dashboard.users_sub') }}</p>
        </div>
        <div class="flex items-center gap-3">
            <!-- Active / Trashed Filter Tabs -->
            <div class="inline-flex rounded-xl bg-slate-100 dark:bg-slate-800 p-1 text-xs font-bold">
                <a href="{{ route('dashboard.users') }}"
                    class="px-3 py-1.5 rounded-lg transition-all {{ !$isTrashed ? 'bg-white dark:bg-slate-900 text-brand-600 dark:text-brand-300 shadow-sm' : 'text-slate-500 hover:text-slate-900 dark:hover:text-white' }}">
                    {{ __('dashboard.active_users') }} ({{ $activeCount }})
                </a>
                <a href="{{ route('dashboard.users', ['trashed' => 1]) }}"
                    class="px-3 py-1.5 rounded-lg transition-all {{ $isTrashed ? 'bg-white dark:bg-slate-900 text-rose-600 dark:text-rose-400 shadow-sm' : 'text-slate-500 hover:text-slate-900 dark:hover:text-white' }}">
                    {{ __('dashboard.deleted_users') }} ({{ $trashedCount }})
                </a>
            </div>

            <button type="button" onclick="document.getElementById('newUserModal').classList.remove('hidden')"
                class="px-4 py-2 rounded-xl bg-brand-600 hover:bg-brand-700 text-white text-xs font-bold shadow-md shadow-indigo-500/20 transition-all flex items-center gap-2">
                <span>➕</span>
                <span>{{ __('dashboard.new_user_btn') }}</span>
            </button>
        </div>
    </div>

    <!-- Users Table Card -->
    <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left rtl:text-right text-sm">
                <thead class="bg-slate-50 dark:bg-slate-800/60 border-b border-slate-200 dark:border-slate-800 text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">
                    <tr>
                        <th class="px-6 py-4">{{ __('dashboard.user_col') }}</th>
                        <th class="px-6 py-4">{{ __('dashboard.department') }}</th>
                        <th class="px-6 py-4">{{ __('dashboard.role_col') }}</th>
                        <th class="px-6 py-4">{{ __('dashboard.user_type_col') }}</th>
                        <th class="px-6 py-4">{{ __('dashboard.responsibilities_col') }}</th>
                        <th class="px-6 py-4">{{ __('dashboard.tasks_activity_col') }}</th>
                        <th class="px-6 py-4 text-right rtl:text-left">{{ __('dashboard.actions_col') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800/60">
                    @forelse($users as $u)
                    <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/30 transition-colors">
                        <!-- User Name & Email & Code -->
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-3">
                                <div class="w-9 h-9 rounded-xl bg-gradient-to-tr from-brand-600 to-indigo-500 text-white font-bold flex items-center justify-center text-xs shadow-sm flex-shrink-0">
                                    {{ strtoupper(substr($u->name, 0, 1)) }}
                                </div>
                                <div>
                                    <div class="flex items-center gap-2">
                                        <span class="font-bold text-slate-900 dark:text-white">{{ $u->name }}</span>
                                        @if($u->code)
                                        <span class="px-2 py-0.5 rounded-md text-[10px] font-mono font-bold bg-indigo-50 text-indigo-700 dark:bg-indigo-950 dark:text-indigo-300 border border-indigo-200 dark:border-indigo-800">
                                            #{{ $u->code }}
                                        </span>
                                        @endif
                                    </div>
                                    <span class="block text-xs text-slate-400">{{ $u->email }}</span>
                                </div>
                            </div>
                        </td>

                        <!-- Department / Project -->
                        <td class="px-6 py-4 text-xs font-semibold text-slate-700 dark:text-slate-300">
                            {{ $u->project?->name ?? '—' }}
                        </td>

                        <!-- Role -->
                        <td class="px-6 py-4">
                            @foreach($u->roles as $role)
                            <span class="px-2.5 py-1 rounded-full text-xs font-bold {{ $role->name === 'admin' ? 'bg-purple-100 text-purple-700 dark:bg-purple-950 dark:text-purple-300' : 'bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-300' }}">
                                {{ ucfirst($role->name) }}
                            </span>
                            @endforeach
                        </td>

                        <!-- User Type -->
                        <td class="px-6 py-4">
                            @if($u->userType)
                            <span class="px-2.5 py-1 rounded-lg text-xs font-extrabold bg-brand-50 text-brand-700 dark:bg-brand-950/60 dark:text-brand-300 border border-brand-200 dark:border-brand-800 uppercase">
                                {{ $u->userType->type }}
                            </span>
                            @else
                            <span class="text-xs text-slate-400 italic">{{ __('dashboard.none') }}</span>
                            @endif
                        </td>

                        <!-- Responsibilities Array -->
                        <td class="px-6 py-4">
                            @if($u->userType && !empty($u->userType->respnsapity))
                            <div class="flex flex-wrap gap-1">
                                @foreach($u->userType->respnsapity as $resp)
                                <span class="px-2 py-0.5 rounded-md text-[11px] font-semibold bg-emerald-50 text-emerald-700 dark:bg-emerald-950 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800">
                                    {{ $resp }}
                                </span>
                                @endforeach
                            </div>
                            @else
                            <span class="text-xs text-slate-400">—</span>
                            @endif
                        </td>

                        <!-- Activity -->
                        <td class="px-6 py-4">
                            <div class="text-xs text-slate-600 dark:text-slate-300 space-y-0.5">
                                <div>{{ __('dashboard.created_count') }} <span class="font-bold text-indigo-600 dark:text-indigo-400">{{ $u->created_tasks_count }}</span></div>
                                <div>{{ __('dashboard.fixed_count') }} <span class="font-bold text-emerald-600 dark:text-emerald-400">{{ $u->fixed_tasks_count }}</span></div>
                            </div>
                        </td>

                        <!-- Actions -->
                        <td class="px-6 py-4 text-right rtl:text-left">
                            <div class="flex items-center justify-end gap-2">
                                @if(!$isTrashed)
                                <button type="button" onclick="openEditModal({{ $u->id }}, '{{ $u->name }}', '{{ $u->userType?->type ?? '' }}', '{{ implode(',', (array)($u->userType?->respnsapity ?? [])) }}')"
                                    class="px-2.5 py-1.5 rounded-xl border border-slate-200 dark:border-slate-700 hover:bg-slate-100 dark:hover:bg-slate-800 text-xs font-bold text-slate-700 dark:text-slate-300 transition-colors">
                                    {{ __('dashboard.edit_type') }}
                                </button>
                                <form action="{{ route('dashboard.users.destroy', $u->id) }}" method="POST" onsubmit="return confirm('{{ __('dashboard.confirm_delete_user') }}')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="px-2.5 py-1.5 rounded-xl border border-rose-200 dark:border-rose-900/60 bg-rose-50/50 dark:bg-rose-950/30 text-rose-600 dark:text-rose-400 hover:bg-rose-100 text-xs font-bold transition-colors">
                                        🗑️ {{ __('dashboard.delete') }}
                                    </button>
                                </form>
                                @else
                                <form action="{{ route('dashboard.users.restore', $u->id) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="px-3 py-1.5 rounded-xl border border-emerald-200 dark:border-emerald-800 bg-emerald-50 dark:bg-emerald-950/40 text-emerald-600 dark:text-emerald-300 hover:bg-emerald-100 text-xs font-bold transition-colors flex items-center gap-1.5">
                                        <span>♻️</span>
                                        <span>{{ __('dashboard.restore') }}</span>
                                    </button>
                                </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-6 py-8 text-center text-slate-400 text-xs font-medium">{{ __('dashboard.no_users_found') }}</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-4 border-t border-slate-200 dark:border-slate-800">
            {{ $users->links() }}
        </div>
    </div>

    <!-- Create User Modal -->
    <div id="newUserModal" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-50 hidden flex items-center justify-center p-4">
        <div class="bg-white dark:bg-slate-900 rounded-2xl max-w-md w-full p-6 border border-slate-200 dark:border-slate-800 shadow-2xl space-y-4">
            <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-3">
                <h3 class="font-bold text-base text-slate-900 dark:text-white">{{ __('dashboard.new_user_btn') }}</h3>
                <button type="button" onclick="document.getElementById('newUserModal').classList.add('hidden')" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200">
                    ✕
                </button>
            </div>

            <form action="{{ route('dashboard.users.store') }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-bold text-slate-600 dark:text-slate-400 mb-1">{{ __('dashboard.user_col') }}</label>
                    <input type="text" name="name" required placeholder="Full Name"
                        class="w-full px-3 py-2 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-900 dark:text-white text-xs font-medium focus:ring-2 focus:ring-brand-500">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-600 dark:text-slate-400 mb-1">{{ __('dashboard.email_address') }}</label>
                    <input type="email" name="email" required placeholder="user@company.com"
                        class="w-full px-3 py-2 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-900 dark:text-white text-xs font-medium focus:ring-2 focus:ring-brand-500">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-600 dark:text-slate-400 mb-1">{{ __('dashboard.password') }}</label>
                    <input type="password" name="password" required placeholder="Minimum 8 characters"
                        class="w-full px-3 py-2 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-900 dark:text-white text-xs font-medium focus:ring-2 focus:ring-brand-500">
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-slate-600 dark:text-slate-400 mb-1">{{ __('dashboard.code') }} ({{ __('dashboard.optional') }})</label>
                        <input type="text" name="code" placeholder="e.g. EMP100"
                            class="w-full px-3 py-2 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-900 dark:text-white text-xs font-medium focus:ring-2 focus:ring-brand-500">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-600 dark:text-slate-400 mb-1">{{ __('dashboard.role_col') }}</label>
                        <select name="role"
                            class="w-full px-3 py-2 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-900 dark:text-white text-xs font-bold focus:ring-2 focus:ring-brand-500">
                            <option value="user">User</option>
                            <option value="admin">Admin</option>
                        </select>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-600 dark:text-slate-400 mb-1">
                        {{ __('dashboard.department') }} <span class="text-rose-500">*</span>
                    </label>
                    <select name="project_id" required
                        class="w-full px-3 py-2 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-900 dark:text-white text-xs font-bold focus:ring-2 focus:ring-brand-500">
                        <option value="">{{ __('dashboard.select_department') }}</option>
                        @foreach($projects as $p)
                        <option value="{{ $p->id }}">{{ $p->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="flex justify-end gap-2 pt-2">
                    <button type="button" onclick="document.getElementById('newUserModal').classList.add('hidden')" class="px-4 py-2 rounded-xl border border-slate-200 dark:border-slate-700 text-xs font-bold text-slate-600 dark:text-slate-400">
                        {{ __('dashboard.cancel') }}
                    </button>
                    <button type="submit" class="px-4 py-2 rounded-xl bg-brand-600 hover:bg-brand-700 text-white text-xs font-bold shadow-md shadow-indigo-500/20">
                        {{ __('dashboard.create_type_btn') }}
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Edit User Type Modal -->
    <div id="userTypeModal" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-50 hidden flex items-center justify-center p-4">
        <div class="bg-white dark:bg-slate-900 rounded-2xl max-w-md w-full p-6 border border-slate-200 dark:border-slate-800 shadow-2xl space-y-4">
            <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-3">
                <h3 class="font-bold text-base text-slate-900 dark:text-white" id="modalUserName">{{ __('dashboard.assign_user_type_modal') }}</h3>
                <button type="button" onclick="closeModal()" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200">
                    ✕
                </button>
            </div>

            <form id="userTypeForm" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-bold text-slate-600 dark:text-slate-400 mb-1">{{ __('dashboard.user_type_col') }}</label>
                    <select name="type" id="modalTypeSelect" required
                        class="w-full px-3 py-2 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-900 dark:text-white text-xs font-bold focus:ring-2 focus:ring-brand-500">
                        <option value="it">{{ __('dashboard.it_role_option') }}</option>
                        <option value="admin">{{ __('dashboard.admin_role_option') }}</option>
                        <option value="manager">{{ __('dashboard.manager_role_option') }}</option>
                        <option value="normal">{{ __('dashboard.normal_role_option') }}</option>
                        <option value="technician">{{ __('dashboard.technician_role_option') }}</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-600 dark:text-slate-400 mb-1">{{ __('dashboard.responsibilities_input_lbl') }}</label>
                    <input type="text" name="respnsapity" id="modalRespInput" placeholder="focus, device, network"
                        class="w-full px-3 py-2 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-900 dark:text-white text-xs font-medium focus:ring-2 focus:ring-brand-500">
                    <span class="text-[11px] text-slate-400 block mt-1">{{ __('dashboard.responsibilities_example') }}</span>
                </div>

                <div class="flex justify-end gap-2 pt-2">
                    <button type="button" onclick="closeModal()" class="px-4 py-2 rounded-xl border border-slate-200 dark:border-slate-700 text-xs font-bold text-slate-600 dark:text-slate-400">
                        {{ __('dashboard.cancel') }}
                    </button>
                    <button type="submit" class="px-4 py-2 rounded-xl bg-brand-600 hover:bg-brand-700 text-white text-xs font-bold shadow-md shadow-indigo-500/20">
                        {{ __('dashboard.save_responsibilities') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function openEditModal(userId, name, type, resp) {
        document.getElementById('modalUserName').innerText = '{{ __('dashboard.edit_user_type_for') }} ' + name;
        document.getElementById('modalTypeSelect').value = type || 'normal';
        document.getElementById('modalRespInput').value = resp || '';
        document.getElementById('userTypeForm').action = '/dashboard/users/' + userId + '/type';
        document.getElementById('userTypeModal').classList.remove('hidden');
    }

    function closeModal() {
        document.getElementById('userTypeModal').classList.add('hidden');
    }
</script>
@endpush
