<nav x-data="{ open: false }">
    @php
        $links = [
            ['label' => 'Dashboard', 'route' => 'dashboard', 'active' => 'dashboard'],
            ['label' => 'Depot', 'route' => 'pos', 'active' => 'pos'],
            ['label' => 'Commandes', 'route' => 'recherche', 'active' => 'recherche'],
            ['label' => 'Clients', 'route' => 'clients.index', 'active' => 'clients.*'],
            ['label' => 'Services', 'route' => 'services.index', 'active' => 'services.*'],
            ['label' => 'Depenses', 'route' => 'depenses.index', 'active' => 'depenses.*'],
        ];
    @endphp

    <div class="md:hidden sticky top-0 z-40 bg-white/90 backdrop-blur border-b border-gray-200">
        <div class="h-16 px-4 flex items-center justify-between">
            <button @click="open = true" class="inline-flex items-center justify-center rounded-md border border-gray-200 p-2 text-slate-600">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                </svg>
            </button>
            <a href="{{ route('dashboard') }}" class="flex items-center gap-2">
                <x-application-logo class="block h-8 w-auto fill-current text-gray-800" />
                <span class="text-sm font-semibold text-slate-700">Pressing</span>
            </a>
            <div class="w-9"></div>
        </div>
    </div>

    <aside class="hidden md:flex fixed inset-y-0 left-0 z-30 w-64 flex-col border-r border-gray-200 bg-white">
        <div class="h-16 px-4 flex items-center border-b border-gray-100">
            <a href="{{ route('dashboard') }}" class="flex items-center gap-2">
                <x-application-logo class="block h-8 w-auto fill-current text-gray-800" />
                <span class="text-base font-semibold text-slate-700">Pressing</span>
            </a>
        </div>

        <div class="flex-1 overflow-y-auto p-3 space-y-1">
            @foreach ($links as $link)
                <a
                    href="{{ route($link['route']) }}"
                    class="block rounded-lg px-3 py-2 text-sm font-medium transition {{ request()->routeIs($link['active']) ? 'bg-blue-50 text-blue-700' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}"
                >
                    {{ $link['label'] }}
                </a>
            @endforeach

            @role('gerant')
                <div class="pt-3 mt-3 border-t border-gray-100 space-y-1">
                    <a href="{{ route('admin.users.index') }}" class="block rounded-lg px-3 py-2 text-sm font-medium transition {{ request()->routeIs('admin.users.*') ? 'bg-blue-50 text-blue-700' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}">Utilisateurs</a>
                    <a href="{{ route('admin.roles.index') }}" class="block rounded-lg px-3 py-2 text-sm font-medium transition {{ request()->routeIs('admin.roles.*') ? 'bg-blue-50 text-blue-700' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}">Roles</a>
                </div>
            @endrole
        </div>

        <div class="border-t border-gray-100 p-3">
            <div class="px-2 pb-2">
                <div class="text-sm font-medium text-slate-800">{{ Auth::user()->name }}</div>
                <div class="text-xs text-slate-500">{{ Auth::user()->email }}</div>
            </div>
            <a href="{{ route('profile.edit') }}" class="block rounded-lg px-3 py-2 text-sm text-slate-600 hover:bg-slate-50">Profil</a>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="mt-1 w-full text-left rounded-lg px-3 py-2 text-sm text-red-600 hover:bg-red-50">Deconnexion</button>
            </form>
        </div>
    </aside>

    <div
        x-show="open"
        x-transition.opacity
        class="md:hidden fixed inset-0 z-40 bg-black/40"
        @click="open = false"
    ></div>

    <aside
        x-show="open"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="-translate-x-full"
        x-transition:enter-end="translate-x-0"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="translate-x-0"
        x-transition:leave-end="-translate-x-full"
        class="md:hidden fixed inset-y-0 left-0 z-50 w-72 flex flex-col border-r border-gray-200 bg-white"
    >
        <div class="h-16 px-4 flex items-center justify-between border-b border-gray-100">
            <a href="{{ route('dashboard') }}" class="flex items-center gap-2">
                <x-application-logo class="block h-8 w-auto fill-current text-gray-800" />
                <span class="text-base font-semibold text-slate-700">Pressing</span>
            </a>
            <button @click="open = false" class="rounded-md border border-gray-200 p-2 text-slate-600">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

        <div class="flex-1 overflow-y-auto p-3 space-y-1">
            @foreach ($links as $link)
                <a
                    href="{{ route($link['route']) }}"
                    @click="open = false"
                    class="block rounded-lg px-3 py-2 text-sm font-medium transition {{ request()->routeIs($link['active']) ? 'bg-blue-50 text-blue-700' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}"
                >
                    {{ $link['label'] }}
                </a>
            @endforeach

            @role('gerant')
                <div class="pt-3 mt-3 border-t border-gray-100 space-y-1">
                    <a href="{{ route('admin.users.index') }}" @click="open = false" class="block rounded-lg px-3 py-2 text-sm font-medium transition {{ request()->routeIs('admin.users.*') ? 'bg-blue-50 text-blue-700' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}">Utilisateurs</a>
                    <a href="{{ route('admin.roles.index') }}" @click="open = false" class="block rounded-lg px-3 py-2 text-sm font-medium transition {{ request()->routeIs('admin.roles.*') ? 'bg-blue-50 text-blue-700' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}">Roles</a>
                </div>
            @endrole
        </div>

        <div class="border-t border-gray-100 p-3">
            <div class="px-2 pb-2">
                <div class="text-sm font-medium text-slate-800">{{ Auth::user()->name }}</div>
                <div class="text-xs text-slate-500">{{ Auth::user()->email }}</div>
            </div>
            <a href="{{ route('profile.edit') }}" @click="open = false" class="block rounded-lg px-3 py-2 text-sm text-slate-600 hover:bg-slate-50">Profil</a>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="mt-1 w-full text-left rounded-lg px-3 py-2 text-sm text-red-600 hover:bg-red-50">Deconnexion</button>
            </form>
        </div>
    </aside>
</nav>
