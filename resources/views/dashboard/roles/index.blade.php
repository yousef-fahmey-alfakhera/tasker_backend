@extends('layouts.dashboard')

@section('title', __('dashboard.roles_title') ?: 'الأدوار والصلاحيات')
@section('header_title', __('dashboard.roles_header') ?: 'إدارة الأدوار والصلاحيات')

@section('content')
<div class="space-y-8">
    <!-- Header & Action Bar -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h2 class="text-lg font-extrabold text-slate-900 dark:text-white">{{ __('dashboard.roles_matrix_title') ?: 'إدارة وتخصيص الأدوار والصلاحيات' }}</h2>
            <p class="text-xs text-slate-500 dark:text-slate-400">إضافة أدوار جديدة، تعديل الصلاحيات الممنوحة لكل دور، واستعراض مصفوفة الأذونات</p>
        </div>
        <div class="flex items-center gap-3">
            <span class="px-3 py-1.5 rounded-xl text-xs font-bold bg-brand-50 text-brand-600 dark:bg-brand-900/30 dark:text-brand-300 border border-brand-200/50 dark:border-brand-800/50">
                {{ $permissions->count() }} {{ __('dashboard.total_permissions') ?: 'صلاحية معرفة' }}
            </span>
            <button type="button" onclick="document.getElementById('newRoleModal').classList.remove('hidden')"
                class="px-4 py-2 rounded-xl bg-brand-600 hover:bg-brand-700 text-white text-xs font-bold shadow-md shadow-indigo-500/20 transition-all flex items-center gap-2">
                <span>➕</span>
                <span>إضافة دور جديد</span>
            </button>
        </div>
    </div>

    <!-- Alert Messages -->
    @if(session('success'))
    <div class="p-4 rounded-xl bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800 text-emerald-800 dark:text-emerald-200 text-xs font-bold flex items-center gap-2">
        <span>✅</span>
        <span>{{ session('success') }}</span>
    </div>
    @endif

    @if(session('error'))
    <div class="p-4 rounded-xl bg-rose-50 dark:bg-rose-950/40 border border-rose-200 dark:border-rose-800 text-rose-800 dark:text-rose-200 text-xs font-bold flex items-center gap-2">
        <span>⚠️</span>
        <span>{{ session('error') }}</span>
    </div>
    @endif

    @if($errors->any())
    <div class="p-4 rounded-xl bg-rose-50 dark:bg-rose-950/40 border border-rose-200 dark:border-rose-800 text-rose-800 dark:text-rose-200 text-xs font-bold space-y-1">
        @foreach($errors->all() as $err)
        <div>• {{ $err }}</div>
        @endforeach
    </div>
    @endif

    <!-- Roles Overview Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
        @foreach($roles as $role)
        @php
            $isSuperAdmin = $role->name === 'admin';
            $isStandardUser = $role->name === 'user';
            $rolePermNames = $role->permissions->pluck('name')->toArray();
        @endphp
        <div class="bg-white dark:bg-slate-900 p-5 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm flex flex-col justify-between transition-all hover:border-brand-500/40">
            <div>
                <div class="flex items-center justify-between mb-3">
                    <span class="px-3 py-1 rounded-full text-xs font-extrabold {{ $isSuperAdmin ? 'bg-purple-100 text-purple-700 dark:bg-purple-950 dark:text-purple-300' : ($isStandardUser ? 'bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-300' : 'bg-brand-50 text-brand-700 dark:bg-brand-950/60 dark:text-brand-300') }}">
                        ROLE: {{ strtoupper($role->name) }}
                    </span>
                    <span class="text-[11px] font-mono text-slate-400">guard: {{ $role->guard_name }}</span>
                </div>

                <h3 class="text-base font-bold text-slate-900 dark:text-white capitalize">
                    {{ $isSuperAdmin ? __('dashboard.super_admin') : ($isStandardUser ? __('dashboard.standard_user') : ucfirst($role->name)) }}
                </h3>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">
                    @if($isSuperAdmin)
                        كامل الصلاحيات الإدارية على كافة أقسام ووظائف النظام
                    @elseif($isStandardUser)
                        المستخدم العادي مع صلاحيات التصفح وإنشاء وإدارة مهامه
                    @else
                        دور مخصص تم إنشاؤه لإدارة صلاحيات محددة
                    @endif
                </p>

                <!-- Granted Privileges Preview -->
                <div class="mt-4 pt-3 border-t border-slate-100 dark:border-slate-800">
                    <div class="flex items-center justify-between text-xs font-semibold mb-2">
                        <span class="text-slate-500">الصلاحيات الممنوحة:</span>
                        <span class="font-extrabold text-brand-600 dark:text-brand-400">
                            {{ $role->permissions->count() }} / {{ $permissions->count() }}
                        </span>
                    </div>

                    <!-- Tags Preview (first 4) -->
                    <div class="flex flex-wrap gap-1 max-h-20 overflow-hidden">
                        @forelse($role->permissions->take(5) as $rp)
                        <span class="px-2 py-0.5 rounded-md text-[10px] font-mono font-medium bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300">
                            {{ $rp->name }}
                        </span>
                        @empty
                        <span class="text-[11px] text-slate-400 italic">لا توجد صلاحيات مسندة</span>
                        @endforelse
                        @if($role->permissions->count() > 5)
                        <span class="px-2 py-0.5 rounded-md text-[10px] font-bold bg-brand-50 dark:bg-brand-950 text-brand-600 dark:text-brand-300">
                            +{{ $role->permissions->count() - 5 }} المزيد
                        </span>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Card Actions -->
            <div class="mt-5 pt-3 border-t border-slate-100 dark:border-slate-800 flex items-center justify-between gap-2">
                <button type="button"
                    onclick="openEditRoleModal({{ json_encode([
                        'id' => $role->id,
                        'name' => $role->name,
                        'is_admin' => $isSuperAdmin,
                        'permissions' => $rolePermNames,
                    ]) }})"
                    class="px-3 py-1.5 rounded-xl bg-indigo-50 hover:bg-indigo-100 text-indigo-700 dark:bg-indigo-950/60 dark:text-indigo-300 dark:hover:bg-indigo-900/50 text-xs font-bold transition-all flex items-center gap-1.5">
                    <span>✏️</span>
                    <span>تعديل الصلاحيات</span>
                </button>

                @if(!$isSuperAdmin && !$isStandardUser)
                <form action="{{ route('dashboard.roles.destroy', $role->id) }}" method="POST" onsubmit="return confirm('هل أنت متأكد من حذف الدور {{ $role->name }}؟')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="px-2.5 py-1.5 rounded-xl border border-rose-200 dark:border-rose-900/60 bg-rose-50/50 dark:bg-rose-950/30 text-rose-600 dark:text-rose-400 hover:bg-rose-100 text-xs font-bold transition-colors" title="حذف الدور">
                        🗑️
                    </button>
                </form>
                @endif
            </div>
        </div>
        @endforeach
    </div>

    <!-- Permissions Matrix by Resource with Interactive Roles Inspection -->
    <div class="space-y-4 pt-4 border-t border-slate-200 dark:border-slate-800">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2">
            <div>
                <h3 class="text-base font-extrabold text-slate-900 dark:text-white">{{ __('dashboard.permissions_by_module') ?: 'مصفوفة الصلاحيات حسب الوحدة (Module)' }}</h3>
                <p class="text-xs text-slate-400">استعراض كافة الصلاحيات والأدوار الممنوحة لكل صلاحية</p>
            </div>
            <!-- Quick Role Filter Tabs -->
            <div class="flex items-center gap-1.5 flex-wrap">
                <span class="text-xs text-slate-500 font-bold ml-1">تصفية حسب الدور:</span>
                <button type="button" onclick="filterMatrixRole('all')" class="matrix-filter-btn px-2.5 py-1 rounded-lg text-xs font-bold bg-brand-600 text-white" data-role="all">الكل</button>
                @foreach($roles as $role)
                <button type="button" onclick="filterMatrixRole('{{ $role->name }}')" class="matrix-filter-btn px-2.5 py-1 rounded-lg text-xs font-bold bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-700" data-role="{{ $role->name }}">
                    {{ ucfirst($role->name) }}
                </button>
                @endforeach
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
            @foreach($groupedPermissions as $resource => $perms)
            <div class="bg-white dark:bg-slate-900 p-5 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm">
                <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-3 mb-3">
                    <h4 class="font-bold text-sm text-slate-900 dark:text-white capitalize flex items-center gap-1.5">
                        <span>📦</span>
                        <span>{{ str_replace('_', ' ', $resource) }}</span>
                    </h4>
                    <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400">
                        {{ $perms->count() }} {{ __('dashboard.actions_word') ?: 'إجراءات' }}
                    </span>
                </div>

                <ul class="space-y-2.5">
                    @foreach($perms as $p)
                    @php
                        $rolesWithThisPerm = $roles->filter(fn($r) => $r->permissions->contains('name', $p->name))->pluck('name')->toArray();
                    @endphp
                    <li class="p-3 rounded-xl bg-slate-50 dark:bg-slate-800/50 border border-slate-100 dark:border-slate-800/80 space-y-1.5 matrix-perm-item" data-roles="{{ json_encode($rolesWithThisPerm) }}">
                        <div class="flex items-center justify-between gap-2">
                            <div>
                                <span class="block font-mono text-xs font-bold text-slate-800 dark:text-slate-200">{{ $p->name }}</span>
                                <span class="block text-[11px] text-slate-500 dark:text-slate-400 font-medium">{{ $p->name_ar ?? 'صلاحية عامة' }}</span>
                            </div>
                            <span class="w-2.5 h-2.5 rounded-full {{ count($rolesWithThisPerm) > 0 ? 'bg-emerald-500' : 'bg-slate-400' }}" title="{{ count($rolesWithThisPerm) }} أدوار تمتلك هذه الصلاحية"></span>
                        </div>
                        <!-- Roles badges possessing this permission -->
                        <div class="flex flex-wrap gap-1 pt-1 border-t border-slate-200/50 dark:border-slate-700/50">
                            @forelse($rolesWithThisPerm as $rName)
                            <span class="px-1.5 py-0.5 rounded text-[9px] font-bold uppercase {{ $rName === 'admin' ? 'bg-purple-100 text-purple-700 dark:bg-purple-950 dark:text-purple-300' : 'bg-indigo-100 text-indigo-700 dark:bg-indigo-950 dark:text-indigo-300' }}">
                                {{ $rName }}
                            </span>
                            @empty
                            <span class="text-[9px] text-slate-400 italic">غير مسندة لأي دور</span>
                            @endforelse
                        </div>
                    </li>
                    @endforeach
                </ul>
            </div>
            @endforeach
        </div>
    </div>

    <!-- Create Role Modal -->
    <div id="newRoleModal" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-50 hidden flex items-center justify-center p-4 overflow-y-auto">
        <div class="bg-white dark:bg-slate-900 rounded-2xl max-w-2xl w-full p-6 border border-slate-200 dark:border-slate-800 shadow-2xl space-y-4 my-8">
            <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-3">
                <div>
                    <h3 class="font-extrabold text-base text-slate-900 dark:text-white">إضافة دور جديد (Create Role)</h3>
                    <p class="text-xs text-slate-400 mt-0.5">حدد اسم الدور واختر الصلاحيات الممنوحة له</p>
                </div>
                <button type="button" onclick="document.getElementById('newRoleModal').classList.add('hidden')" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200">
                    ✕
                </button>
            </div>

            <form action="{{ route('dashboard.roles.store') }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-bold text-slate-600 dark:text-slate-400 mb-1">اسم الدور <span class="text-rose-500">*</span></label>
                    <input type="text" name="name" required placeholder="مثال: supervisor أو manager"
                        class="w-full px-3 py-2 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-900 dark:text-white text-xs font-bold focus:ring-2 focus:ring-brand-500">
                    <p class="text-[11px] text-slate-400 mt-1">يُفضل استخدام أحرف إنجليزية صغيرة بدون مسافات (مثل: supervisor)</p>
                </div>

                <div class="pt-2 border-t border-slate-100 dark:border-slate-800">
                    <div class="flex items-center justify-between mb-2">
                        <label class="block text-xs font-extrabold text-slate-700 dark:text-slate-300">
                            الصلاحيات الممنوحة لهذا الدور
                        </label>
                        <div class="flex items-center gap-2">
                            <button type="button" onclick="toggleAllModalCheckboxes('newRoleModal', true)" class="text-[11px] font-bold text-brand-600 hover:underline">تحديد الكل</button>
                            <span class="text-slate-300">|</span>
                            <button type="button" onclick="toggleAllModalCheckboxes('newRoleModal', false)" class="text-[11px] font-bold text-slate-400 hover:underline">إلغاء التحديد</button>
                        </div>
                    </div>

                    <div class="max-h-72 overflow-y-auto space-y-3 p-3 rounded-xl bg-slate-50 dark:bg-slate-800/60 border border-slate-200 dark:border-slate-700">
                        @foreach($groupedPermissions as $resource => $perms)
                        <div class="p-2.5 rounded-lg bg-white dark:bg-slate-900 border border-slate-100 dark:border-slate-800 space-y-2">
                            <div class="flex items-center justify-between pb-1.5 border-b border-slate-100 dark:border-slate-800">
                                <span class="font-bold text-xs text-slate-900 dark:text-white capitalize">📦 {{ str_replace('_', ' ', $resource) }}</span>
                                <button type="button" onclick="toggleModuleCheckboxes(this, true)" class="text-[10px] text-indigo-500 hover:underline font-bold">تحديد الوحدة</button>
                            </div>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                                @foreach($perms as $p)
                                <label class="flex items-center gap-2 text-xs font-medium text-slate-700 dark:text-slate-300 cursor-pointer hover:text-indigo-600">
                                    <input type="checkbox" name="permissions[]" value="{{ $p->name }}" class="rounded text-brand-600 focus:ring-brand-500 module-perm-chk">
                                    <span class="font-mono text-[11px]">{{ $p->name }}</span>
                                </label>
                                @endforeach
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>

                <div class="flex justify-end gap-2 pt-3 border-t border-slate-100 dark:border-slate-800">
                    <button type="button" onclick="document.getElementById('newRoleModal').classList.add('hidden')" class="px-4 py-2 rounded-xl border border-slate-200 dark:border-slate-700 text-xs font-bold text-slate-600 dark:text-slate-400">
                        إلغاء
                    </button>
                    <button type="submit" class="px-5 py-2 rounded-xl bg-brand-600 hover:bg-brand-700 text-white text-xs font-bold shadow-md shadow-indigo-500/20">
                        حفظ الدور الجديد
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Edit Role & Permissions Modal -->
    <div id="editRoleModal" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-50 hidden flex items-center justify-center p-4 overflow-y-auto">
        <div class="bg-white dark:bg-slate-900 rounded-2xl max-w-2xl w-full p-6 border border-slate-200 dark:border-slate-800 shadow-2xl space-y-4 my-8">
            <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-3">
                <div>
                    <h3 class="font-extrabold text-base text-slate-900 dark:text-white" id="editRoleModalTitle">تعديل الدور والصلاحيات</h3>
                    <p class="text-xs text-slate-400 mt-0.5">تحديث صلاحيات هذا الدور وسيتم تطبيقها على كافة المستخدمين المنتمين له</p>
                </div>
                <button type="button" onclick="document.getElementById('editRoleModal').classList.add('hidden')" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200">
                    ✕
                </button>
            </div>

            <form id="editRoleForm" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-bold text-slate-600 dark:text-slate-400 mb-1">اسم الدور</label>
                    <input type="text" name="name" id="editRoleNameInput" required
                        class="w-full px-3 py-2 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-900 dark:text-white text-xs font-bold focus:ring-2 focus:ring-brand-500">
                    <p class="text-[11px] text-slate-400 mt-1" id="editRoleNotice"></p>
                </div>

                <div class="pt-2 border-t border-slate-100 dark:border-slate-800">
                    <div class="flex items-center justify-between mb-2">
                        <label class="block text-xs font-extrabold text-slate-700 dark:text-slate-300">
                            الصلاحيات المسندة لهذا الدور
                        </label>
                        <div class="flex items-center gap-2">
                            <button type="button" onclick="toggleAllModalCheckboxes('editRoleModal', true)" class="text-[11px] font-bold text-brand-600 hover:underline">تحديد الكل</button>
                            <span class="text-slate-300">|</span>
                            <button type="button" onclick="toggleAllModalCheckboxes('editRoleModal', false)" class="text-[11px] font-bold text-slate-400 hover:underline">إلغاء التحديد</button>
                        </div>
                    </div>

                    <div class="max-h-72 overflow-y-auto space-y-3 p-3 rounded-xl bg-slate-50 dark:bg-slate-800/60 border border-slate-200 dark:border-slate-700">
                        @foreach($groupedPermissions as $resource => $perms)
                        <div class="p-2.5 rounded-lg bg-white dark:bg-slate-900 border border-slate-100 dark:border-slate-800 space-y-2">
                            <div class="flex items-center justify-between pb-1.5 border-b border-slate-100 dark:border-slate-800">
                                <span class="font-bold text-xs text-slate-900 dark:text-white capitalize">📦 {{ str_replace('_', ' ', $resource) }}</span>
                                <button type="button" onclick="toggleModuleCheckboxes(this, true)" class="text-[10px] text-indigo-500 hover:underline font-bold">تحديد الوحدة</button>
                            </div>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                                @foreach($perms as $p)
                                <label class="flex items-center gap-2 text-xs font-medium text-slate-700 dark:text-slate-300 cursor-pointer hover:text-indigo-600">
                                    <input type="checkbox" name="permissions[]" value="{{ $p->name }}" id="edit_perm_{{ str_replace(':', '_', $p->name) }}" class="rounded text-brand-600 focus:ring-brand-500 edit-role-perm-chk">
                                    <span class="font-mono text-[11px]">{{ $p->name }}</span>
                                </label>
                                @endforeach
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>

                <div class="flex justify-end gap-2 pt-3 border-t border-slate-100 dark:border-slate-800">
                    <button type="button" onclick="document.getElementById('editRoleModal').classList.add('hidden')" class="px-4 py-2 rounded-xl border border-slate-200 dark:border-slate-700 text-xs font-bold text-slate-600 dark:text-slate-400">
                        إلغاء
                    </button>
                    <button type="submit" class="px-5 py-2 rounded-xl bg-brand-600 hover:bg-brand-700 text-white text-xs font-bold shadow-md shadow-indigo-500/20">
                        حفظ التعديلات
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function toggleAllModalCheckboxes(modalId, checked) {
        const modal = document.getElementById(modalId);
        if (!modal) return;
        const checkboxes = modal.querySelectorAll('input[type="checkbox"][name="permissions[]"]');
        checkboxes.forEach(cb => cb.checked = checked);
    }

    function toggleModuleCheckboxes(btn, defaultVal) {
        const parent = btn.closest('.rounded-lg');
        if (!parent) return;
        const checkboxes = parent.querySelectorAll('input[type="checkbox"]');
        const anyUnchecked = Array.from(checkboxes).some(cb => !cb.checked);
        checkboxes.forEach(cb => cb.checked = anyUnchecked);
    }

    function openEditRoleModal(role) {
        document.getElementById('editRoleModalTitle').innerText = 'تعديل الدور والصلاحيات: ' + role.name;
        const nameInput = document.getElementById('editRoleNameInput');
        const notice = document.getElementById('editRoleNotice');
        nameInput.value = role.name;

        if (role.is_admin) {
            nameInput.readOnly = true;
            notice.innerText = 'دور المسؤول الرئيسي (admin) لا يمكن تغيير اسمه للحفاظ على سلامة النظام.';
        } else {
            nameInput.readOnly = false;
            notice.innerText = '';
        }

        // Reset all checkboxes in edit modal
        const allCheckboxes = document.querySelectorAll('.edit-role-perm-chk');
        allCheckboxes.forEach(cb => cb.checked = false);

        // Check assigned permissions
        if (role.permissions && Array.isArray(role.permissions)) {
            role.permissions.forEach(pName => {
                const safeId = 'edit_perm_' + pName.replace(/:/g, '_');
                const cb = document.getElementById(safeId);
                if (cb) cb.checked = true;
            });
        }

        document.getElementById('editRoleForm').action = '/dashboard/roles/' + role.id + '/update';
        document.getElementById('editRoleModal').classList.remove('hidden');
    }

    function filterMatrixRole(roleName) {
        // Update filter buttons styles
        const buttons = document.querySelectorAll('.matrix-filter-btn');
        buttons.forEach(btn => {
            if (btn.getAttribute('data-role') === roleName) {
                btn.className = 'matrix-filter-btn px-2.5 py-1 rounded-lg text-xs font-bold bg-brand-600 text-white';
            } else {
                btn.className = 'matrix-filter-btn px-2.5 py-1 rounded-lg text-xs font-bold bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-700';
            }
        });

        const items = document.querySelectorAll('.matrix-perm-item');
        items.forEach(item => {
            if (roleName === 'all') {
                item.classList.remove('opacity-30', 'scale-[0.98]');
                return;
            }
            try {
                const assignedRoles = JSON.parse(item.getAttribute('data-roles') || '[]');
                if (assignedRoles.includes(roleName)) {
                    item.classList.remove('opacity-30', 'scale-[0.98]');
                } else {
                    item.classList.add('opacity-30', 'scale-[0.98]');
                }
            } catch {
                // Ignore parse error
            }
        });
    }
</script>
@endpush
