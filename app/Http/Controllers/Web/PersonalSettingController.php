<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Models\UserSetting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class PersonalSettingController extends Controller
{
    /**
     * Display personal settings page.
     */
    public function index(): View
    {
        $user = Auth::guard('web')->user()->loadMissing(['userSettings.setting', 'userType']);

        // Fetch all available global settings
        $allSettings = Setting::all();

        // Key-value map of user settings
        $userPreferences = [];
        foreach ($user->userSettings as $userSetting) {
            if ($userSetting->setting) {
                $userPreferences[$userSetting->setting->name] = $userSetting->casted_value;
            }
        }

        // Active theme mode
        $activeTheme = $userPreferences['theme_mode'] ?? 'light';

        return view('dashboard.settings.index', compact('user', 'allSettings', 'userPreferences', 'activeTheme'));
    }

    /**
     * Update personal settings & profile.
     */
    public function update(Request $request): RedirectResponse
    {
        $user = Auth::guard('web')->user();

        $validated = $request->validate([
            'name'       => ['required', 'string', 'max:255'],
            'email'      => ['required', 'email', Rule::unique('users', 'email')->ignore($user->id)],
            'theme_mode' => ['required', Rule::in(['light', 'dark'])],
            'language'   => ['nullable', Rule::in(['en', 'ar'])],
            'password'   => ['nullable', 'string', 'min:8', 'confirmed'],
        ]);

        $user->name = $validated['name'];
        $user->email = $validated['email'];
        if (! empty($validated['password'])) {
            $user->password = Hash::make($validated['password']);
        }
        $user->save();

        // Save theme setting
        $themeSetting = Setting::firstOrCreate(
            ['name' => 'theme_mode'],
            ['type' => 'string', 'default' => 'light']
        );
        UserSetting::updateOrCreate(
            ['user_id' => $user->id, 'setting_id' => $themeSetting->id],
            ['value' => $validated['theme_mode']]
        );

        // Save light_mode boolean helper
        $lightSetting = Setting::firstOrCreate(
            ['name' => 'light_mode'],
            ['type' => 'bool', 'default' => true]
        );
        UserSetting::updateOrCreate(
            ['user_id' => $user->id, 'setting_id' => $lightSetting->id],
            ['value' => $validated['theme_mode'] === 'light' ? '1' : '0']
        );

        // Save preferred language setting
        if (! empty($validated['language'])) {
            $langSetting = Setting::firstOrCreate(
                ['name' => 'language'],
                ['type' => 'string', 'default' => 'en']
            );
            UserSetting::updateOrCreate(
                ['user_id' => $user->id, 'setting_id' => $langSetting->id],
                ['value' => $validated['language']]
            );
            session(['locale' => $validated['language']]);
        }

        // Put active theme in session
        session(['theme_mode' => $validated['theme_mode']]);

        return back()->with('success', 'Personal settings updated successfully.');
    }

    /**
     * AJAX quick theme toggle between light and dark mode.
     */
    public function toggleTheme(Request $request): JsonResponse
    {
        $user = Auth::guard('web')->user();
        $targetTheme = $request->input('theme');

        if (! in_array($targetTheme, ['light', 'dark'])) {
            $current = session('theme_mode', 'light');
            $targetTheme = $current === 'dark' ? 'light' : 'dark';
        }

        if ($user) {
            $themeSetting = Setting::firstOrCreate(
                ['name' => 'theme_mode'],
                ['type' => 'string', 'default' => 'light']
            );
            UserSetting::updateOrCreate(
                ['user_id' => $user->id, 'setting_id' => $themeSetting->id],
                ['value' => $targetTheme]
            );

            $lightSetting = Setting::firstOrCreate(
                ['name' => 'light_mode'],
                ['type' => 'bool', 'default' => true]
            );
            UserSetting::updateOrCreate(
                ['user_id' => $user->id, 'setting_id' => $lightSetting->id],
                ['value' => $targetTheme === 'light' ? '1' : '0']
            );
        }

        session(['theme_mode' => $targetTheme]);

        return response()->json([
            'success'    => true,
            'theme_mode' => $targetTheme,
        ]);
    }

    /**
     * Quick switch language (en / ar) from navbar.
     */
    public function switchLocale(Request $request): RedirectResponse
    {
        $locale = $request->input('locale');
        if (in_array($locale, ['en', 'ar'])) {
            $user = Auth::guard('web')->user();
            if ($user) {
                $langSetting = Setting::firstOrCreate(
                    ['name' => 'language'],
                    ['type' => 'string', 'default' => 'en']
                );
                UserSetting::updateOrCreate(
                    ['user_id' => $user->id, 'setting_id' => $langSetting->id],
                    ['value' => $locale]
                );
            }
            session(['locale' => $locale]);
        }

        return back();
    }
}
