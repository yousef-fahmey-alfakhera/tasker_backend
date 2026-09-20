<?php

namespace Tests\Feature\Web;

use App\Models\Project;
use App\Models\Setting;
use App\Models\Task;
use App\Models\TaskStatus;
use App\Models\TaskType;
use App\Models\User;
use App\Models\UserType;
use App\Models\Workspace;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardWebTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $regularUser;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\PermissionSeeder::class);
        $this->seed(\Database\Seeders\TaskStatusSeeder::class);
        $this->seed(\Database\Seeders\TaskTypeSeeder::class);

        $this->admin = User::where('email', 'admin@admin.com')->first();
        $this->regularUser = User::where('email', 'user@user.com')->first();
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $response = $this->get('/dashboard');
        $response->assertRedirect('/login');

        $rootResponse = $this->get('/');
        $rootResponse->assertRedirect('/login');
    }

    public function test_login_page_renders_with_quick_demo_buttons(): void
    {
        $response = $this->get('/login');
        $response->assertStatus(200)
            ->assertSee('Sign In to Dashboard')
            ->assertSee('Quick Admin')
            ->assertSee('Quick User');
    }

    public function test_quick_login_logs_in_user_and_redirects_to_dashboard(): void
    {
        $response = $this->post('/login/quick', ['role' => 'admin']);
        $response->assertRedirect('/dashboard');
        $this->assertAuthenticatedAs($this->admin, 'web');
    }

    public function test_authenticated_user_can_access_dashboard_with_default_date_filter(): void
    {
        $project = Project::create([
            'name'       => 'Cloud Platform',
            'created_by' => $this->admin->id,
        ]);

        $workspace = Workspace::create([
            'project_id' => $project->id,
            'name'       => 'DevOps Sprint',
            'created_by' => $this->admin->id,
        ]);

        $status = TaskStatus::first();
        $taskType = TaskType::first();

        // Assign UserType to admin (it, responsibilities)
        UserType::create([
            'user_id'     => $this->admin->id,
            'type'        => 'it',
            'respnsapity' => ['network', 'device'],
        ]);

        // Create tasks
        Task::create([
            'created_by'   => $this->admin->id,
            'fixed_by'     => $this->admin->id,
            'project_id'   => $project->id,
            'workspace_id' => $workspace->id,
            'status_id'    => $status->id,
            'task_type_id' => $taskType->id,
            'title'        => 'Configure Cloud Firewall',
            'priority'     => 'Urgent',
        ]);

        $response = $this->actingAs($this->admin, 'web')->get('/dashboard');

        $yesterday = Carbon::yesterday()->format('Y-m-d');
        $tomorrow = Carbon::tomorrow()->format('Y-m-d');

        $response->assertStatus(200)
            ->assertSee('Analytics')
            ->assertSee('Filter Timeline Range')
            ->assertSee($yesterday)
            ->assertSee($tomorrow)
            ->assertSee('DevOps Sprint')
            ->assertSee('Configure Cloud Firewall')
            ->assertSee('Active Solvers')
            ->assertSee('Active Complaints');
    }

    public function test_custom_date_filter_on_dashboard(): void
    {
        $from = Carbon::now()->subDays(5)->format('Y-m-d');
        $to = Carbon::now()->addDays(2)->format('Y-m-d');

        $response = $this->actingAs($this->admin, 'web')
            ->get("/dashboard?from={$from}&to={$to}");

        $response->assertStatus(200)
            ->assertSee($from)
            ->assertSee($to);
    }

    public function test_user_management_page_and_type_update(): void
    {
        $response = $this->actingAs($this->admin, 'web')->get('/dashboard/users');
        $response->assertStatus(200)
            ->assertSee('Responsibilities Directory')
            ->assertSee($this->admin->name)
            ->assertSee($this->regularUser->name);

        // Update user type and responsibilities via POST
        $updateResponse = $this->actingAs($this->admin, 'web')
            ->post("/dashboard/users/{$this->regularUser->id}/type", [
                'type'        => 'technician',
                'respnsapity' => 'network, device, focus',
            ]);

        $updateResponse->assertRedirect();
        $this->assertDatabaseHas('user_types', [
            'user_id' => $this->regularUser->id,
            'type'    => 'technician',
        ]);

        $freshUserType = UserType::where('user_id', $this->regularUser->id)->first();
        $this->assertEquals(['network', 'device', 'focus'], $freshUserType->respnsapity);
    }

    public function test_roles_and_permissions_page(): void
    {
        $response = $this->actingAs($this->admin, 'web')->get('/dashboard/roles');
        $response->assertStatus(200)
            ->assertSee('Permissions Matrix')
            ->assertSee('Super Administrator')
            ->assertSee('Standard User')
            ->assertSee('show_tasks')
            ->assertSee('show_task_types')
            ->assertSee('show_user_types');
    }

    public function test_workspaces_and_projects_page(): void
    {
        $response = $this->actingAs($this->admin, 'web')->get('/dashboard/workspaces');
        $response->assertStatus(200)
            ->assertSee('Workspaces')
            ->assertSee('Active Projects');
    }

    public function test_tasks_management_page_and_type_creation(): void
    {
        $response = $this->actingAs($this->admin, 'web')->get('/dashboard/tasks');
        $response->assertStatus(200)
            ->assertSee('Configured Task Types')
            ->assertSee('Workflow Statuses');

        // Store new task type
        $typeResponse = $this->actingAs($this->admin, 'web')->post('/dashboard/tasks/types', [
            'name' => 'Database Optimization',
            'type' => 'focus',
        ]);

        $typeResponse->assertRedirect();
        $this->assertDatabaseHas('task_types', [
            'name' => 'Database Optimization',
            'type' => 'focus',
        ]);
    }

    public function test_personal_settings_and_theme_toggle(): void
    {
        $response = $this->actingAs($this->admin, 'web')->get('/dashboard/settings');
        $response->assertStatus(200)
            ->assertSee('Personal Profile & Interface Preferences')
            ->assertSee('Light Mode')
            ->assertSee('Dark Mode');

        // Update settings via form
        $updateResponse = $this->actingAs($this->admin, 'web')->post('/dashboard/settings', [
            'name'       => 'Admin Updated',
            'email'      => 'admin@admin.com',
            'theme_mode' => 'dark',
            'language'   => 'en',
        ]);

        $updateResponse->assertRedirect();
        $this->assertEquals('dark', session('theme_mode'));

        // Toggle theme via AJAX endpoint
        $ajaxResponse = $this->actingAs($this->admin, 'web')->postJson('/dashboard/settings/toggle-theme', [
            'theme' => 'light',
        ]);

        $ajaxResponse->assertStatus(200)
            ->assertJson([
                'success'    => true,
                'theme_mode' => 'light',
            ]);

        $this->assertEquals('light', session('theme_mode'));
    }

    public function test_user_get_preferred_locale_resolves_from_setting_or_default(): void
    {
        // 1. Ensure Setting table has language default = 'en'
        $langSetting = Setting::updateOrCreate(
            ['name' => 'language'],
            ['type' => 'string', 'default' => 'en']
        );

        // When user has no specific setting, defaults to settings table default ('en')
        $this->assertEquals('en', $this->regularUser->getPreferredLocale());

        // 2. When settings table default is changed to 'ar', user gets 'ar'
        $langSetting->update(['default' => 'ar']);
        $this->assertEquals('ar', $this->regularUser->getPreferredLocale());

        // 3. When user has their own explicit user_setting ('en'), it overrides table default ('ar')
        \App\Models\UserSetting::updateOrCreate(
            ['user_id' => $this->regularUser->id, 'setting_id' => $langSetting->id],
            ['value' => 'en']
        );
        $this->assertEquals('en', $this->regularUser->fresh()->getPreferredLocale());

        // 4. When user updates to 'ar'
        \App\Models\UserSetting::updateOrCreate(
            ['user_id' => $this->regularUser->id, 'setting_id' => $langSetting->id],
            ['value' => 'ar']
        );
        $this->assertEquals('ar', $this->regularUser->fresh()->getPreferredLocale());
    }

    public function test_dashboard_renders_rtl_when_user_preferred_locale_is_arabic(): void
    {
        $langSetting = Setting::updateOrCreate(
            ['name' => 'language'],
            ['type' => 'string', 'default' => 'en']
        );

        // Set user setting to Arabic
        \App\Models\UserSetting::updateOrCreate(
            ['user_id' => $this->admin->id, 'setting_id' => $langSetting->id],
            ['value' => 'ar']
        );

        $response = $this->actingAs($this->admin, 'web')->get('/dashboard');
        $response->assertStatus(200)
            ->assertSee('dir="rtl"', false)
            ->assertSee('lang="ar"', false);
    }

    public function test_switch_locale_endpoint_persists_and_switches_direction(): void
    {
        // Switch to Arabic
        $switchArResponse = $this->actingAs($this->admin, 'web')->post('/dashboard/settings/switch-locale', [
            'locale' => 'ar',
        ]);
        $switchArResponse->assertRedirect();
        $this->assertEquals('ar', session('locale'));
        $this->assertEquals('ar', $this->admin->fresh()->getPreferredLocale());

        // Verify dashboard renders RTL
        $dashboardAr = $this->actingAs($this->admin, 'web')->get('/dashboard');
        $dashboardAr->assertStatus(200)
            ->assertSee('dir="rtl"', false)
            ->assertSee('lang="ar"', false);

        // Switch back to English
        $switchEnResponse = $this->actingAs($this->admin, 'web')->post('/dashboard/settings/switch-locale', [
            'locale' => 'en',
        ]);
        $switchEnResponse->assertRedirect();
        $this->assertEquals('en', session('locale'));
        $this->assertEquals('en', $this->admin->fresh()->getPreferredLocale());

        // Verify dashboard renders LTR
        $dashboardEn = $this->actingAs($this->admin, 'web')->get('/dashboard');
        $dashboardEn->assertStatus(200)
            ->assertSee('dir="ltr"', false)
            ->assertSee('lang="en"', false);
    }

    public function test_all_dashboard_pages_render_in_arabic_when_setting_is_arabic(): void
    {
        $langSetting = Setting::updateOrCreate(
            ['name' => 'language'],
            ['type' => 'string', 'default' => 'en']
        );

        \App\Models\UserSetting::updateOrCreate(
            ['user_id' => $this->admin->id, 'setting_id' => $langSetting->id],
            ['value' => 'ar']
        );

        $authAdmin = $this->actingAs($this->admin, 'web');

        // 1. Home Dashboard
        $homeRes = $authAdmin->get('/dashboard');
        $homeRes->assertStatus(200)
            ->assertSee('تصفية النطاق الزمني')
            ->assertSee('المهام خلال الفترة')
            ->assertSee('المخطط الزمني لنشاط المهام')
            ->assertSee('لوحات مهام مساحات العمل');

        // 2. Users Page
        $usersRes = $authAdmin->get('/dashboard/users');
        $usersRes->assertStatus(200)
            ->assertSee('جميع مستخدمي المنصة')
            ->assertSee('المستخدم')
            ->assertSee('نوع المستخدم')
            ->assertSee('المسؤوليات التخصصية');

        // 3. Roles Page
        $rolesRes = $authAdmin->get('/dashboard/roles');
        $rolesRes->assertStatus(200)
            ->assertSee('مصفوفة الأدوار والصلاحيات')
            ->assertSee('إجمالي الصلاحيات')
            ->assertSee('الصلاحيات الممنوحة:');

        // 4. Workspaces Page
        $workspacesRes = $authAdmin->get('/dashboard/workspaces');
        $workspacesRes->assertStatus(200)
            ->assertSee('المشاريع النشطة')
            ->assertSee('مساحات العمل المعينة')
            ->assertSee('المشروع التابع له');

        // 5. Tasks Page
        $tasksRes = $authAdmin->get('/dashboard/tasks');
        $tasksRes->assertStatus(200)
            ->assertSee('أنواع المهام المهيأة')
            ->assertSee('حالات ومراحل سير العمل')
            ->assertSee('دليل المهام');

        // 6. Settings Page
        $settingsRes = $authAdmin->get('/dashboard/settings');
        $settingsRes->assertStatus(200)
            ->assertSee('تفضيلات الحساب والعرض')
            ->assertSee('المظهر ونمط الواجهة')
            ->assertSee('معلومات الملف الشخصي')
            ->assertSee('الأمان وكلمة المرور');
    }
}
