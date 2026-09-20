@extends('layouts.dashboard')

@section('title', __('dashboard.settings_title'))
@section('header_title', __('dashboard.settings_header'))

@section('content')
<div class="max-w-4xl space-y-8">
    <div>
        <h2 class="text-lg font-extrabold text-slate-900 dark:text-white">{{ __('dashboard.account_display_prefs') }}</h2>
        <p class="text-xs text-slate-500 dark:text-slate-400">{{ __('dashboard.settings_sub') }}</p>
    </div>

    <form action="{{ route('dashboard.settings.update') }}" method="POST" class="space-y-6">
        @csrf

        <!-- Card 1: Theme & Display Preferences -->
        <div class="bg-white dark:bg-slate-900 rounded-2xl p-6 border border-slate-200 dark:border-slate-800 shadow-sm space-y-5">
            <div class="flex items-center gap-3 border-b border-slate-100 dark:border-slate-800 pb-4">
                <div class="w-10 h-10 rounded-xl bg-amber-50 dark:bg-amber-950 text-amber-600 dark:text-amber-400 flex items-center justify-center font-bold text-lg">
                    🎨
                </div>
                <div>
                    <h3 class="font-bold text-sm text-slate-900 dark:text-white">{{ __('dashboard.theme_interface_mode') }}</h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400">{{ __('dashboard.theme_desc') }}</p>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <!-- Light Mode Card Option -->
                <label class="cursor-pointer relative flex flex-col p-4 rounded-xl border-2 transition-all {{ ($activeTheme ?? 'light') === 'light' ? 'border-brand-600 bg-brand-50/20 dark:bg-brand-950/20' : 'border-slate-200 dark:border-slate-800 hover:border-slate-300' }}">
                    <input type="radio" name="theme_mode" value="light" class="sr-only" {{ ($activeTheme ?? 'light') === 'light' ? 'checked' : '' }}
                        onchange="document.documentElement.classList.remove('dark'); localStorage.setItem('tasker_theme', 'light');">
                    <div class="flex items-center justify-between mb-3">
                        <span class="text-xs font-bold text-slate-900 dark:text-white flex items-center gap-2">
                            {{ __('dashboard.light_mode') }}
                        </span>
                        <span class="w-4 h-4 rounded-full border-2 flex items-center justify-center {{ ($activeTheme ?? 'light') === 'light' ? 'border-brand-600 bg-brand-600' : 'border-slate-300' }}">
                            <span class="w-1.5 h-1.5 rounded-full bg-white"></span>
                        </span>
                    </div>
                    <p class="text-xs text-slate-500">{{ __('dashboard.light_mode_desc') }}</p>
                </label>

                <!-- Dark Mode Card Option -->
                <label class="cursor-pointer relative flex flex-col p-4 rounded-xl border-2 transition-all {{ ($activeTheme ?? 'light') === 'dark' ? 'border-brand-600 bg-brand-50/20 dark:bg-brand-950/20' : 'border-slate-200 dark:border-slate-800 hover:border-slate-300' }}">
                    <input type="radio" name="theme_mode" value="dark" class="sr-only" {{ ($activeTheme ?? 'light') === 'dark' ? 'checked' : '' }}
                        onchange="document.documentElement.classList.add('dark'); localStorage.setItem('tasker_theme', 'dark');">
                    <div class="flex items-center justify-between mb-3">
                        <span class="text-xs font-bold text-slate-900 dark:text-white flex items-center gap-2">
                            {{ __('dashboard.dark_mode') }}
                        </span>
                        <span class="w-4 h-4 rounded-full border-2 flex items-center justify-center {{ ($activeTheme ?? 'light') === 'dark' ? 'border-brand-600 bg-brand-600' : 'border-slate-300' }}">
                            <span class="w-1.5 h-1.5 rounded-full bg-white"></span>
                        </span>
                    </div>
                    <p class="text-xs text-slate-500">{{ __('dashboard.dark_mode_desc') }}</p>
                </label>
            </div>
        </div>

        <!-- Card 2: Profile Details -->
        <div class="bg-white dark:bg-slate-900 rounded-2xl p-6 border border-slate-200 dark:border-slate-800 shadow-sm space-y-5">
            <div class="flex items-center gap-3 border-b border-slate-100 dark:border-slate-800 pb-4">
                <div class="w-10 h-10 rounded-xl bg-brand-50 dark:bg-brand-950 text-brand-600 dark:text-brand-400 flex items-center justify-center font-bold text-lg">
                    👤
                </div>
                <div>
                    <h3 class="font-bold text-sm text-slate-900 dark:text-white">{{ __('dashboard.profile_information') }}</h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400">{{ __('dashboard.profile_desc') }}</p>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-slate-600 dark:text-slate-400 mb-1">{{ __('dashboard.full_name') }}</label>
                    <input type="text" name="name" value="{{ old('name', $user->name) }}" required
                        class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-900 dark:text-white text-xs font-semibold focus:ring-2 focus:ring-brand-500 focus:outline-none">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-600 dark:text-slate-400 mb-1">{{ __('dashboard.email_address') }}</label>
                    <input type="email" name="email" value="{{ old('email', $user->email) }}" required
                        class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-900 dark:text-white text-xs font-semibold focus:ring-2 focus:ring-brand-500 focus:outline-none">
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-600 dark:text-slate-400 mb-1">{{ __('dashboard.preferred_language') }}</label>
                <select name="language" class="w-full sm:w-64 px-3.5 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-900 dark:text-white text-xs font-semibold">
                    <option value="en" {{ ($userPreferences['language'] ?? $user->getPreferredLocale()) === 'en' ? 'selected' : '' }}>{{ __('dashboard.lang_english') }}</option>
                    <option value="ar" {{ ($userPreferences['language'] ?? $user->getPreferredLocale()) === 'ar' ? 'selected' : '' }}>{{ __('dashboard.lang_arabic') }}</option>
                </select>
            </div>
        </div>

        <!-- Card 3: Security & Password -->
        <div class="bg-white dark:bg-slate-900 rounded-2xl p-6 border border-slate-200 dark:border-slate-800 shadow-sm space-y-5">
            <div class="flex items-center gap-3 border-b border-slate-100 dark:border-slate-800 pb-4">
                <div class="w-10 h-10 rounded-xl bg-purple-50 dark:bg-purple-950 text-purple-600 dark:text-purple-400 flex items-center justify-center font-bold text-lg">
                    🔒
                </div>
                <div>
                    <h3 class="font-bold text-sm text-slate-900 dark:text-white">{{ __('dashboard.security_password') }}</h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400">{{ __('dashboard.leave_blank_hint') }}</p>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-slate-600 dark:text-slate-400 mb-1">{{ __('dashboard.new_password') }}</label>
                    <input type="password" name="password" placeholder="••••••••"
                        class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-900 dark:text-white text-xs font-medium focus:ring-2 focus:ring-brand-500 focus:outline-none">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-600 dark:text-slate-400 mb-1">{{ __('dashboard.confirm_password') }}</label>
                    <input type="password" name="password_confirmation" placeholder="••••••••"
                        class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-900 dark:text-white text-xs font-medium focus:ring-2 focus:ring-brand-500 focus:outline-none">
                </div>
            </div>
        </div>

        <!-- Save Button -->
        <div class="flex justify-end gap-3">
            <button type="submit" id="savePersonalSettingsBtn" class="px-6 py-3 rounded-xl bg-gradient-to-r from-brand-600 to-indigo-600 text-white text-xs font-extrabold shadow-lg shadow-indigo-500/25 hover:opacity-95 transition-all">
                {{ __('dashboard.save_changes') }}
            </button>
        </div>
    </form>
</div>
@endsection
