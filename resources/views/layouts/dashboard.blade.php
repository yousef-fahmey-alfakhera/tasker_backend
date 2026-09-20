<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') - Tasker Management</title>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Tailwind CSS Play CDN with dark mode config -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Plus Jakarta Sans', 'sans-serif'],
                    },
                    colors: {
                        brand: {
                            50: '#eef2ff',
                            100: '#e0e7ff',
                            500: '#6366f1',
                            600: '#4f46e5',
                            700: '#4338ca',
                            900: '#312e81',
                        }
                    }
                }
            }
        }
    </script>

    <!-- Chart.js CDN -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <!-- Theme Initialization Script (Avoid FOUC) -->
    <script>
        (function() {
            const sessionTheme = @json(session('theme_mode'));
            const localTheme = localStorage.getItem('tasker_theme');
            const active = localTheme || sessionTheme || 'light';
            if (active === 'dark') {
                document.documentElement.classList.add('dark');
            } else {
                document.documentElement.classList.remove('dark');
            }
        })();
    </script>

    <style>
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
        }
        /* Custom scrollbar */
        ::-webkit-scrollbar {
            width: 6px;
            height: 6px;
        }
        ::-webkit-scrollbar-track {
            background: transparent;
        }
        ::-webkit-scrollbar-thumb {
            background: rgba(156, 163, 175, 0.4);
            border-radius: 9999px;
        }
        .dark ::-webkit-scrollbar-thumb {
            background: rgba(75, 85, 99, 0.5);
        }
    </style>
</head>
<body class="h-full bg-slate-50 text-slate-800 dark:bg-slate-950 dark:text-slate-100 antialiased transition-colors duration-200">
    <div class="min-h-full flex flex-col lg:flex-row">

        <!-- Sidebar -->
        <aside id="sidebar" class="w-full lg:w-72 bg-white dark:bg-slate-900 border-r rtl:border-r-0 rtl:border-l border-slate-200 dark:border-slate-800 flex-shrink-0 flex flex-col z-30 transition-all duration-200">
            <!-- Brand Logo -->
            <div class="h-20 flex items-center justify-between px-6 border-b border-slate-200 dark:border-slate-800">
                <a href="{{ route('dashboard.index') }}" class="flex items-center gap-3 group">
                    <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-brand-600 to-indigo-400 flex items-center justify-center text-white font-black text-xl shadow-lg shadow-indigo-500/20 group-hover:scale-105 transition-transform">
                        T
                    </div>
                    <div>
                        <span class="font-extrabold text-xl tracking-tight text-slate-900 dark:text-white">{{ __('dashboard.brand_name') }}</span>
                        <span class="block text-[11px] font-semibold text-brand-600 dark:text-brand-400 uppercase tracking-wider">{{ __('dashboard.enterprise_os') }}</span>
                    </div>
                </a>

                <!-- Mobile menu toggle button -->
                <button id="mobileMenuBtn" class="lg:hidden p-2 rounded-lg text-slate-500 hover:bg-slate-100 dark:hover:bg-slate-800">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path></svg>
                </button>
            </div>

            <!-- Navigation Links -->
            <nav class="flex-1 px-4 py-6 space-y-1.5 overflow-y-auto">
                <div class="px-3 pb-2 text-[11px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider">
                    {{ __('dashboard.nav_core') }}
                </div>

                <!-- 1. Home / Overview -->
                <a href="{{ route('dashboard.index') }}" id="nav-home" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl font-semibold text-sm transition-all {{ request()->routeIs('dashboard.index') ? 'bg-brand-50 text-brand-600 dark:bg-brand-500/10 dark:text-brand-400 shadow-sm' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800/60 hover:text-slate-900 dark:hover:text-white' }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path></svg>
                    <span>{{ __('dashboard.nav_home') }}</span>
                </a>

                <div class="pt-5 px-3 pb-2 text-[11px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider">
                    {{ __('dashboard.nav_management') }}
                </div>

                <!-- 2. User Management -->
                <a href="{{ route('dashboard.users') }}" id="nav-users" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl font-semibold text-sm transition-all {{ request()->routeIs('dashboard.users*') ? 'bg-brand-50 text-brand-600 dark:bg-brand-500/10 dark:text-brand-400 shadow-sm' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800/60 hover:text-slate-900 dark:hover:text-white' }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                    <span>{{ __('dashboard.nav_users') }}</span>
                </a>

                <!-- 3. Roles & Permissions -->
                <a href="{{ route('dashboard.roles') }}" id="nav-roles" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl font-semibold text-sm transition-all {{ request()->routeIs('dashboard.roles*') ? 'bg-brand-50 text-brand-600 dark:bg-brand-500/10 dark:text-brand-400 shadow-sm' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800/60 hover:text-slate-900 dark:hover:text-white' }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path></svg>
                    <span>{{ __('dashboard.nav_roles') }}</span>
                </a>

                <!-- 4. Workspaces & Projects -->
                <a href="{{ route('dashboard.workspaces') }}" id="nav-workspaces" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl font-semibold text-sm transition-all {{ request()->routeIs('dashboard.workspaces*') ? 'bg-brand-50 text-brand-600 dark:bg-brand-500/10 dark:text-brand-400 shadow-sm' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800/60 hover:text-slate-900 dark:hover:text-white' }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
                    <span>{{ __('dashboard.nav_workspaces') }}</span>
                </a>

                <!-- 5. Tasks Management -->
                <a href="{{ route('dashboard.tasks') }}" id="nav-tasks" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl font-semibold text-sm transition-all {{ request()->routeIs('dashboard.tasks*') ? 'bg-brand-50 text-brand-600 dark:bg-brand-500/10 dark:text-brand-400 shadow-sm' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800/60 hover:text-slate-900 dark:hover:text-white' }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path></svg>
                    <span>{{ __('dashboard.nav_tasks') }}</span>
                </a>

                <div class="pt-5 px-3 pb-2 text-[11px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider">
                    {{ __('dashboard.nav_preferences') }}
                </div>

                <!-- 6. Personal Settings -->
                <a href="{{ route('dashboard.settings') }}" id="nav-settings" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl font-semibold text-sm transition-all {{ request()->routeIs('dashboard.settings*') ? 'bg-brand-50 text-brand-600 dark:bg-brand-500/10 dark:text-brand-400 shadow-sm' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800/60 hover:text-slate-900 dark:hover:text-white' }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                    <span>{{ __('dashboard.nav_settings') }}</span>
                </a>
            </nav>

            <!-- User Footer Profile Card -->
            <div class="p-4 border-t border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-900/50">
                @if(auth()->check())
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-3 overflow-hidden">
                        <div class="w-9 h-9 rounded-full bg-gradient-to-tr from-brand-600 to-indigo-500 text-white font-bold flex items-center justify-center flex-shrink-0 text-sm shadow">
                            {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                        </div>
                        <div class="overflow-hidden">
                            <p class="font-bold text-xs truncate text-slate-900 dark:text-white">{{ auth()->user()->name }}</p>
                            <span class="inline-block px-1.5 py-0.5 rounded text-[10px] font-semibold bg-brand-100 text-brand-700 dark:bg-brand-900/50 dark:text-brand-300">
                                {{ auth()->user()->userType?->type ? strtoupper(auth()->user()->userType->type) : 'ADMIN' }}
                            </span>
                        </div>
                    </div>
                    <form action="{{ route('logout') }}" method="POST">
                        @csrf
                        <button type="submit" title="{{ __('dashboard.logout') }}" class="p-2 text-slate-400 hover:text-rose-600 dark:hover:text-rose-400 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
                        </button>
                    </form>
                </div>
                @endif
            </div>
        </aside>

        <!-- Main Content Area -->
        <div class="flex-1 flex flex-col min-w-0">

            <!-- Top Header Navbar -->
            <header class="h-20 bg-white/80 dark:bg-slate-900/80 backdrop-blur-md border-b border-slate-200 dark:border-slate-800 px-6 lg:px-8 flex items-center justify-between sticky top-0 z-20">
                <div class="flex items-center gap-3">
                    <h1 class="text-xl font-extrabold text-slate-900 dark:text-white tracking-tight">
                        @yield('header_title', __('dashboard.header_title_overview'))
                    </h1>
                    @yield('header_badge')
                </div>

                <div class="flex items-center gap-3">
                    <!-- Light / Dark Mode Toggle Button -->
                    <button id="themeToggleBtn" type="button" class="p-2.5 rounded-xl border border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 transition-all flex items-center gap-2 shadow-sm" title="{{ __('dashboard.theme') }}">
                        <!-- Sun Icon (shown in dark mode) -->
                        <svg id="sunIcon" class="w-5 h-5 hidden dark:block text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                        <!-- Moon Icon (shown in light mode) -->
                        <svg id="moonIcon" class="w-5 h-5 block dark:hidden text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"></path></svg>
                        <span id="themeLabel" class="text-xs font-bold hidden sm:inline">{{ __('dashboard.theme') }}</span>
                    </button>

                    <!-- Language Switcher Button -->
                    <form action="{{ route('dashboard.settings.switch_locale') }}" method="POST" class="inline">
                        @csrf
                        <input type="hidden" name="locale" value="{{ app()->getLocale() === 'ar' ? 'en' : 'ar' }}">
                        <button type="submit" class="p-2.5 rounded-xl border border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 transition-all flex items-center gap-1.5 text-xs font-bold shadow-sm" title="{{ __('dashboard.switch_language') }}">
                            <span class="text-sm">🌐</span>
                            <span class="hidden sm:inline">{{ app()->getLocale() === 'ar' ? 'English (LTR)' : 'العربية (RTL)' }}</span>
                        </button>
                    </form>

                    <!-- Quick Status / Time Pill -->
                    <div class="hidden sm:flex items-center gap-2 px-3 py-1.5 rounded-xl bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 text-xs font-semibold">
                        <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                        <span>{{ __('dashboard.api_live') }}</span>
                    </div>
                </div>
            </header>

            <!-- Notification Messages -->
            @if(session('success'))
            <div class="mx-6 lg:mx-8 mt-6 p-4 rounded-xl bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800 text-emerald-800 dark:text-emerald-300 text-sm font-semibold flex items-center justify-between shadow-sm">
                <div class="flex items-center gap-2">
                    <svg class="w-5 h-5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path></svg>
                    <span>{{ session('success') }}</span>
                </div>
            </div>
            @endif

            @if(session('error') || $errors->any())
            <div class="mx-6 lg:mx-8 mt-6 p-4 rounded-xl bg-rose-50 dark:bg-rose-950/40 border border-rose-200 dark:border-rose-800 text-rose-800 dark:text-rose-300 text-sm font-semibold flex items-center justify-between shadow-sm">
                <div class="flex items-center gap-2">
                    <svg class="w-5 h-5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path></svg>
                    <span>{{ session('error') ?? $errors->first() }}</span>
                </div>
            </div>
            @endif

            <!-- Main Page Content -->
            <main class="flex-1 p-6 lg:p-8">
                @yield('content')
            </main>
        </div>
    </div>

    <!-- Interactive Theme & UI Script -->
    <script>
        // Toggle theme button
        const themeBtn = document.getElementById('themeToggleBtn');
        if (themeBtn) {
            themeBtn.addEventListener('click', async function() {
                const isDark = document.documentElement.classList.toggle('dark');
                const target = isDark ? 'dark' : 'light';
                localStorage.setItem('tasker_theme', target);

                try {
                    await fetch('{{ route("dashboard.settings.toggle_theme") }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        },
                        body: JSON.stringify({ theme: target })
                    });
                } catch(e) {
                    console.log('Theme saved locally');
                }
            });
        }

        // Mobile menu toggle
        const mobileBtn = document.getElementById('mobileMenuBtn');
        const sidebar = document.getElementById('sidebar');
        if (mobileBtn && sidebar) {
            mobileBtn.addEventListener('click', () => {
                sidebar.classList.toggle('hidden');
            });
        }
    </script>
    @stack('scripts')
</body>
</html>
