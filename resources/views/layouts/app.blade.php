<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}" class="h-full bg-gray-50">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? config('app.name', 'مغسلة النظيف') }}</title>
    <style>
        .num-ltr {
            direction: ltr;
            unicode-bidi: isolate;
            display: inline-block;
            text-align: left;
        }
    </style>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="stylesheet" href="https://cdn-uicons.flaticon.com/2.6.0/uicons-regular-rounded/css/uicons-regular-rounded.css">
    @livewireStyles
    <style>
        .nav-progress {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            height: 3px;
            z-index: 9999;
            background: transparent;
            pointer-events: none;
        }
        .nav-progress-bar {
            height: 100%;
            width: 0%;
            background: linear-gradient(90deg, #3b82f6, #2563eb, #1d4ed8);
            border-radius: 0 2px 2px 0;
            transition: width 0.3s ease;
            box-shadow: 0 0 8px rgba(37, 99, 235, 0.5);
        }
        .nav-progress-bar.loading {
            animation: progressLoad 1.8s ease-in-out forwards;
        }
        .nav-progress-bar.done {
            width: 100% !important;
            transition: width 0.15s ease;
            animation: none;
        }
        @keyframes progressLoad {
            0%   { width: 0%; }
            20%  { width: 30%; }
            50%  { width: 55%; }
            80%  { width: 78%; }
            100% { width: 90%; }
        }
    </style>
</head>
<body class="app-shell">
    <div class="nav-progress" id="navProgress"><div class="nav-progress-bar" id="navProgressBar"></div></div>
    <?php $isRtl = app()->getLocale() === 'ar'; ?>
    <div
        class="flex h-full"
        x-data="{
            sidebarOpen: true,
            isRtl: {{ $isRtl ? 'true' : 'false' }},
            userMenu: false,
            openLogoutConfirm: false,
            show: false,
            type: 'success',
            message: '',
            timeout: null,
            notify(payload) {
                this.type = payload.type ?? 'success';
                this.message = payload.message ?? '';
                this.show = true;
                clearTimeout(this.timeout);
                this.timeout = setTimeout(() => this.show = false, 3500);
            },
            confirmerDeconnexion() {
                this.openLogoutConfirm = false;
                this.$refs.logoutForm.requestSubmit();
            }
        }"
        x-init="
            @if (session('success'))
                notify({ type: 'success', message: @js(session('success')) });
            @endif
            @if (session('error'))
                notify({ type: 'error', message: @js(session('error')) });
            @endif
            window.addEventListener('notify', event => notify(event.detail || {}));
        "
    >
        <aside
            class="z-30 hidden h-screen overflow-hidden md:flex md:shrink-0 flex-col border-slate-800 bg-slate-900 shadow-xl transition-all duration-300 {{ $isRtl ? 'border-l' : 'border-r' }}"
            :class="sidebarOpen ? 'w-64' : 'w-20'"
        >
            <div class="flex h-16 items-center px-4 border-b border-slate-700">
                <a href="{{ route('dashboard') }}" wire:navigate class="flex items-center gap-3 min-w-0">
                    <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-blue-600 font-bold text-white text-lg shrink-0">
                        ب
                    </div>
                    <span x-show="sidebarOpen" x-transition class="text-white font-semibold text-lg truncate">{{ config('app.name') }}</span>
                </a>
            </div>

            <nav class="scrollbar-hidden flex-1 overflow-y-auto px-3 py-4 space-y-1">
                @include('layouts.partials.menu-links', [
                    'menuItemClass' => 'flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium transition-colors',
                    'menuActiveClass' => 'bg-blue-600 text-white',
                    'menuInactiveClass' => 'text-slate-300 hover:bg-slate-800 hover:text-white',
                    'menuSectionClass' => 'pt-4',
                    'withCollapse' => true,
                ])
            </nav>

        </aside>

        <div class="flex min-w-0 flex-1 flex-col transition-all duration-300">
            <header class="sticky top-0 z-40 flex h-12 items-center justify-between border-b border-slate-200 bg-white/95 px-3 sm:px-4 backdrop-blur-sm">
                <?php
                    $sectionMeta = match (true) {
                        request()->routeIs('dashboard') => ['label' => 'لوحة التحكم', 'hint' => 'نظرة عامة'],
                        request()->routeIs('pos') => ['label' => 'إيداع الملابس', 'hint' => 'إيداع جديد'],
                        request()->routeIs('recherche') => ['label' => 'الطلبات', 'hint' => 'البحث والمتابعة'],
                        request()->routeIs('clients.*') => ['label' => 'الزبناء', 'hint' => 'إدارة الزبناء'],
                        request()->routeIs('services.*') => ['label' => 'الخدمات', 'hint' => 'قائمة الخدمات'],
                        request()->routeIs('depenses.*') => ['label' => 'المصروفات', 'hint' => 'المصروفات'],
                        request()->routeIs('finances.*') => ['label' => 'الإيرادات والمصروفات', 'hint' => 'المالية'],
                        request()->routeIs('parametrage.*') => ['label' => 'الإعدادات', 'hint' => 'تهيئة النظام'],
                        request()->routeIs('admin.*') => ['label' => 'الإدارة', 'hint' => 'المستخدمون'],
                        default => ['label' => 'التطبيق', 'hint' => ''],
                    };
                    $pageTitle = $title ?? $sectionMeta['label'];
                    $isGerant = auth()->user()?->hasRole('gerant');
                    $succursales = $isGerant ? \App\Models\Succursale::query()->where('actif', true)->orderBy('nom')->get(['id', 'nom']) : collect();
                    $activeSuccursaleId = session('active_succursale_id');
                ?>
                <div class="min-w-0 flex items-center gap-2">
                    <button
                        type="button"
                        @click="if (window.matchMedia('(min-width: 768px)').matches) { sidebarOpen = !sidebarOpen } else { $dispatch('open-mobile-sidebar') }"
                        class="inline-flex items-center justify-center rounded p-1.5 text-slate-500 hover:bg-slate-100 hover:text-slate-700"
                        title="القائمة"
                    >
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5M3.75 17.25h16.5" />
                        </svg>
                    </button>
                    <h1 class="truncate text-sm font-semibold text-gray-900">{{ $pageTitle }}</h1>
                    <span class="hidden text-[11px] text-slate-400 lg:inline">{{ $sectionMeta['hint'] }}</span>
                </div>

                <div class="flex items-center gap-2">
                    @if($isGerant)
                        <form method="POST" action="{{ route('succursales.active') }}">
                            @csrf
                            <select name="succursale_id" onchange="this.form.submit()" class="rounded border-slate-300 text-xs py-1 px-2 leading-tight">
                                <option value="">جميع الفروع</option>
                                @foreach($succursales as $succursale)
                                    <option value="{{ $succursale->id }}" @selected((int) $activeSuccursaleId === (int) $succursale->id)>
                                        {{ $succursale->nom }}
                                    </option>
                                @endforeach
                            </select>
                        </form>
                    @else
                        <span class="hidden sm:inline-flex rounded-full bg-slate-100 px-2 py-0.5 text-[11px] text-slate-500">
                            {{ auth()->user()?->succursale?->nom ?? 'بدون فرع' }}
                        </span>
                    @endif

                    <div class="relative">
                        <button type="button" @click="userMenu = !userMenu" class="flex items-center gap-1.5 rounded px-2 py-1 text-xs text-gray-600 hover:bg-gray-100 transition-colors">
                            <div class="flex h-6 w-6 items-center justify-center rounded-full bg-blue-100 text-blue-700 text-[10px] font-semibold">
                                {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                            </div>
                            <span class="hidden sm:inline text-xs">{{ auth()->user()->name }}</span>
                            <svg class="h-3 w-3 text-gray-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" /></svg>
                        </button>
                        <div x-show="userMenu" @click.away="userMenu = false" x-transition :class="isRtl ? 'left-0' : 'right-0'" class="absolute mt-1 w-48 rounded-lg bg-white py-1 shadow-lg ring-1 ring-black/5">
                            <div class="px-3 py-1.5 border-b">
                                <p class="text-[11px] text-gray-500">{{ auth()->user()->getRoleNames()->first() ?? 'مستخدم' }}</p>
                                <p class="text-[10px] text-gray-400">{{ auth()->user()->email }}</p>
                            </div>
                            <a href="{{ route('profile.edit') }}" wire:navigate class="block px-3 py-1.5 text-xs text-gray-700 hover:bg-gray-50">الملف الشخصي</a>
                            <form method="POST" action="{{ route('logout') }}" x-ref="logoutForm">
                                @csrf
                                <button type="button" @click="openLogoutConfirm = true; userMenu = false" :class="isRtl ? 'text-right' : 'text-left'" class="block w-full px-3 py-1.5 text-xs text-red-600 hover:bg-red-50">
                                    تسجيل الخروج
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </header>

            <main class="flex-1 overflow-y-auto px-2 py-3 sm:px-4 sm:py-4 lg:px-5">
                {{ $slot }}
            </main>
        </div>

        <div
            class="md:hidden"
            x-data="{ openMobile: false }"
            @open-mobile-sidebar.window="openMobile = true"
        >
            <div x-show="openMobile" x-transition.opacity class="fixed inset-0 z-[60] bg-black/40" @click="openMobile = false"></div>
            <aside
                x-show="openMobile"
                x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="{{ $isRtl ? 'translate-x-full' : '-translate-x-full' }}"
                x-transition:enter-end="translate-x-0"
                x-transition:leave="transition ease-in duration-150"
                x-transition:leave-start="translate-x-0"
                x-transition:leave-end="{{ $isRtl ? 'translate-x-full' : '-translate-x-full' }}"
                class="scrollbar-hidden fixed inset-y-0 {{ $isRtl ? 'right-0' : 'left-0' }} z-[70] w-72 bg-slate-900 text-slate-200 p-4 space-y-2 overflow-y-auto"
            >
                <div class="flex items-center justify-between mb-3">
                    <div class="font-semibold text-white">القائمة</div>
                    <button type="button" @click="openMobile = false" class="rounded-md border border-slate-700 p-1.5 text-slate-300">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                @include('layouts.partials.menu-links', [
                    'menuItemClass' => 'block rounded-lg px-3 py-2',
                    'menuActiveClass' => 'bg-blue-600 text-white',
                    'menuInactiveClass' => 'hover:bg-slate-800',
                    'menuSectionClass' => 'pt-2 px-1 text-xs uppercase tracking-wider text-slate-400',
                    'withCollapse' => false,
                ])
            </aside>
        </div>

        <div
            x-cloak
            x-show="show"
            x-transition
            class="fixed top-4 z-[100] max-w-sm rounded-lg border px-4 py-3 shadow-lg"
            :class="[
                isRtl ? 'left-4' : 'right-4',
                type === 'error'
                    ? 'bg-red-50 border-red-300 text-red-800'
                    : 'bg-green-50 border-green-300 text-green-800'
            ]"
        >
            <div class="text-sm font-medium" x-text="message"></div>
        </div>

        <div x-cloak x-show="openLogoutConfirm" x-transition class="modal-overlay flex items-center justify-center p-4">
            <div class="modal-panel max-w-md p-4 space-y-3">
                <h3 class="text-base font-semibold text-slate-900">تأكيد تسجيل الخروج</h3>
                <p class="text-sm text-slate-600">هل تريد تسجيل الخروج الآن؟</p>
                <div class="flex justify-end gap-2">
                    <button type="button" class="btn-secondary" @click="openLogoutConfirm = false">إلغاء</button>
                    <button type="button" class="btn-danger" @click="confirmerDeconnexion">تسجيل الخروج</button>
                </div>
            </div>
        </div>

    </div>
    @livewireScripts
    <script>
        (function() {
            const bar = document.getElementById('navProgressBar');
            if (!bar) return;

            document.addEventListener('livewire:navigate', () => {
                bar.classList.remove('done');
                bar.style.width = '0%';
                void bar.offsetWidth;
                bar.classList.add('loading');
            });

            document.addEventListener('livewire:navigated', () => {
                bar.classList.remove('loading');
                bar.classList.add('done');
                setTimeout(() => {
                    bar.classList.remove('done');
                    bar.style.width = '0%';
                }, 350);
            });
        })();
    </script>
</body>
</html>
