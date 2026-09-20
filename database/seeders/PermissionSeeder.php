<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $guard = 'sanctum';

        $cruds = [
            'tasks' => [
                'show'   => 'عرض المهام',
                'create' => 'إنشاء المهام',
                'update' => 'تعديل المهام',
                'delete' => 'حذف المهام',
            ],
            'projects' => [
                'show'   => 'عرض المشاريع',
                'create' => 'إنشاء المشاريع',
                'update' => 'تعديل المشاريع',
                'delete' => 'حذف المشاريع',
            ],
            'workspaces' => [
                'show'   => 'عرض مساحات العمل',
                'create' => 'إنشاء مساحات العمل',
                'update' => 'تعديل مساحات العمل',
                'delete' => 'حذف مساحات العمل',
            ],
            'task_statuses' => [
                'show'   => 'عرض حالات المهام',
                'create' => 'إنشاء حالات المهام',
                'update' => 'تعديل حالات المهام',
                'delete' => 'حذف حالات المهام',
            ],
            'attachments' => [
                'show'   => 'عرض المرفقات',
                'create' => 'إنشاء المرفقات',
                'update' => 'تعديل المرفقات',
                'delete' => 'حذف المرفقات',
            ],
            'settings' => [
                'show'   => 'عرض الإعدادات',
                'create' => 'إنشاء الإعدادات',
                'update' => 'تعديل الإعدادات',
                'delete' => 'حذف الإعدادات',
            ],
            'user_settings' => [
                'show'   => 'عرض إعدادات المستخدم',
                'create' => 'إنشاء إعدادات المستخدم',
                'update' => 'تعديل إعدادات المستخدم',
                'delete' => 'حذف إعدادات المستخدم',
            ],
            'task_types' => [
                'show'   => 'عرض أنواع المهام',
                'create' => 'إنشاء أنواع المهام',
                'update' => 'تعديل أنواع المهام',
                'delete' => 'حذف أنواع المهام',
            ],
            'user_types' => [
                'show'   => 'عرض أنواع المستخدمين',
                'create' => 'إنشاء أنواع المستخدمين',
                'update' => 'تعديل أنواع المستخدمين',
                'delete' => 'حذف أنواع المستخدمين',
            ],
        ];

        $allPermissionInstances = [];

        foreach ($cruds as $resource => $actions) {
            foreach ($actions as $action => $arabicLabel) {
                $permissionName = "{$action}_{$resource}";

                $permission = Permission::updateOrCreate(
                    [
                        'name'       => $permissionName,
                        'guard_name' => $guard,
                    ],
                    [
                        'name_ar'    => $arabicLabel,
                    ]
                );

                $allPermissionInstances[] = $permission;
            }
        }

        // Create Admin Role and assign all permissions
        $adminRole = Role::firstOrCreate([
            'name'       => 'admin',
            'guard_name' => $guard,
        ]);
        $adminRole->syncPermissions($allPermissionInstances);

        // Create User Role and assign standard permissions
        $userRole = Role::firstOrCreate([
            'name'       => 'user',
            'guard_name' => $guard,
        ]);

        $userPermissions = Permission::where('guard_name', $guard)
            ->where(function ($query) {
                $query->where('name', 'like', 'show_%')
                    ->orWhere('name', 'create_tasks')
                    ->orWhere('name', 'update_tasks')
                    ->orWhere('name', 'create_attachments')
                    ->orWhere('name', 'show_user_settings')
                    ->orWhere('name', 'create_user_settings')
                    ->orWhere('name', 'update_user_settings')
                    ->orWhere('name', 'delete_user_settings');
            })
            ->get();
        $userRole->syncPermissions($userPermissions);

        // Create Default Admin User
        $adminUser = User::updateOrCreate(
            ['email' => 'admin@admin.com'],
            [
                'name'     => 'Admin User',
                'password' => Hash::make('123456789'),
            ]
        );
        $adminUser->syncRoles([$adminRole]);

        // Create Default Regular User
        $regularUser = User::updateOrCreate(
            ['email' => 'user@user.com'],
            [
                'name'     => 'Regular User',
                'password' => Hash::make('123456789'),
            ]
        );
        $regularUser->syncRoles([$userRole]);
    }
}
