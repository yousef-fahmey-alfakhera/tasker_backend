<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Tasker Enterprise OS</title>
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    fontFamily: { sans: ['Plus Jakarta Sans', 'sans-serif'] },
                    colors: {
                        brand: {
                            500: '#6366f1',
                            600: '#4f46e5',
                            700: '#4338ca',
                        }
                    }
                }
            }
        }
    </script>
    <script>
        if (localStorage.getItem('tasker_theme') === 'dark') {
            document.documentElement.classList.add('dark');
        }
    </script>
</head>
<body class="h-full bg-slate-100 dark:bg-slate-950 flex items-center justify-center p-4 transition-colors">
    <div class="w-full max-w-md">
        <!-- Brand Header -->
        <div class="text-center mb-8">
            <div class="inline-flex w-14 h-14 rounded-2xl bg-gradient-to-tr from-brand-600 to-indigo-400 items-center justify-center text-white font-extrabold text-2xl shadow-xl shadow-indigo-500/30 mb-3">
                T
            </div>
            <h1 class="text-3xl font-extrabold text-slate-900 dark:text-white tracking-tight">{{ __('dashboard.welcome_to_tasker') }}</h1>
            <p class="text-sm font-medium text-slate-500 dark:text-slate-400 mt-1">{{ __('dashboard.enterprise_management_sub') }}</p>
        </div>

        <!-- Login Card -->
        <div class="bg-white dark:bg-slate-900 rounded-3xl p-8 shadow-xl shadow-slate-200/50 dark:shadow-none border border-slate-200 dark:border-slate-800">
            @if(session('error') || $errors->any())
            <div class="mb-5 p-3.5 rounded-xl bg-rose-50 dark:bg-rose-950/40 border border-rose-200 dark:border-rose-800 text-rose-700 dark:text-rose-300 text-xs font-semibold">
                {{ session('error') ?? $errors->first() }}
            </div>
            @endif

            <form action="{{ route('login.post') }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-2">{{ __('dashboard.email_address') }}</label>
                    <input type="email" name="email" id="emailInput" value="{{ old('email', 'admin@admin.com') }}" required autofocus
                        class="w-full px-4 py-3 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-brand-500 text-sm font-medium transition-all">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-2">{{ __('dashboard.password') }}</label>
                    <input type="password" name="password" id="passwordInput" value="123456789" required
                        class="w-full px-4 py-3 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-brand-500 text-sm font-medium transition-all">
                </div>

                <button type="submit" id="submitLoginBtn" class="w-full py-3.5 px-4 rounded-xl bg-gradient-to-r from-brand-600 to-indigo-600 text-white font-bold text-sm shadow-lg shadow-indigo-500/25 hover:opacity-95 transition-all">
                    {{ __('dashboard.sign_in_title') }}
                </button>
            </form>

            <div class="relative my-6 text-center">
                <div class="absolute inset-0 flex items-center"><div class="w-full border-t border-slate-200 dark:border-slate-800"></div></div>
                <span class="relative bg-white dark:bg-slate-900 px-3 text-xs font-bold uppercase tracking-wider text-slate-400">{{ __('dashboard.quick_demo_header') }}</span>
            </div>

            <!-- One-Click Quick Login Buttons -->
            <div class="grid grid-cols-2 gap-3">
                <form action="{{ route('login.quick') }}" method="POST">
                    @csrf
                    <input type="hidden" name="role" value="admin">
                    <button type="submit" id="quickAdminBtn" class="w-full py-2.5 px-3 rounded-xl border border-indigo-200 dark:border-indigo-900/60 bg-indigo-50/50 dark:bg-indigo-950/30 text-indigo-700 dark:text-indigo-300 text-xs font-bold hover:bg-indigo-100 dark:hover:bg-indigo-900/40 transition-all">
                        ⚡ {{ __('dashboard.quick_admin') }}
                    </button>
                </form>

                <form action="{{ route('login.quick') }}" method="POST">
                    @csrf
                    <input type="hidden" name="role" value="user">
                    <button type="submit" id="quickUserBtn" class="w-full py-2.5 px-3 rounded-xl border border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-800 text-slate-700 dark:text-slate-300 text-xs font-bold hover:bg-slate-100 dark:hover:bg-slate-700 transition-all">
                        👤 {{ __('dashboard.quick_user') }}
                    </button>
                </form>
            </div>
        </div>
    </div>
</body>
</html>
