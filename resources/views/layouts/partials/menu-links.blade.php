@php
    $mainLinks = [
        ['route' => 'dashboard', 'match' => 'dashboard', 'label' => 'لوحة التحكم', 'icon' => 'dashboard'],
        ['route' => 'pos', 'match' => 'pos', 'label' => 'إيداع الملابس', 'icon' => 'depot'],
        ['route' => 'recherche', 'match' => 'recherche', 'label' => 'الطلبات', 'icon' => 'commandes'],
        ['route' => 'clients.index', 'match' => 'clients.*', 'label' => 'الزبناء', 'icon' => 'clients'],
        ['route' => 'depenses.index', 'match' => 'depenses.*', 'label' => 'المصروفات', 'icon' => 'depenses'],
        ['route' => 'finances.recettes-depenses', 'match' => 'finances.*', 'label' => 'الإيرادات والمصروفات', 'icon' => 'finances'],
    ];

    $parametrageLinks = [
        ['route' => 'services.index', 'match' => 'services.*', 'label' => 'الخدمات', 'icon' => 'services'],
        ['route' => 'parametrage.stock-consommables.index', 'match' => 'parametrage.stock-consommables.*', 'label' => 'المخزون', 'icon' => 'stock'],
        ['route' => 'parametrage.employes.index', 'match' => 'parametrage.employes.*', 'label' => 'الموظفون', 'icon' => 'employes'],
        ['route' => 'parametrage.modes-paiement.index', 'match' => 'parametrage.modes-paiement.*', 'label' => 'طرق الدفع', 'icon' => 'modes'],
        ['route' => 'parametrage.fournisseurs.index', 'match' => 'parametrage.fournisseurs.*', 'label' => 'الموردون', 'icon' => 'fournisseurs'],
        ['route' => 'parametrage.types-depenses.index', 'match' => 'parametrage.types-depenses.*', 'label' => 'أنواع المصروفات', 'icon' => 'types'],
    ];

    $adminLinks = [
        ['route' => 'admin.succursales.index', 'match' => 'admin.succursales.*', 'label' => 'الفروع', 'icon' => 'succursales'],
        ['route' => 'admin.users.index', 'match' => 'admin.users.*', 'label' => 'المستخدمون', 'icon' => 'users'],
    ];

    $iconMap = [
        'dashboard'   => 'fi-rr-dashboard',
        'depot'       => 'fi-rr-clothes-hanger',
        'commandes'   => 'fi-rr-clipboard-list',
        'clients'     => 'fi-rr-users',
        'depenses'    => 'fi-rr-money-bill-wave',
        'finances'    => 'fi-rr-chart-line-up',
        'services'    => 'fi-rr-star',
        'stock'       => 'fi-rr-box-open',
        'employes'    => 'fi-rr-user-gear',
        'modes'       => 'fi-rr-credit-card',
        'fournisseurs'=> 'fi-rr-truck-side',
        'types'       => 'fi-rr-tags',
        'succursales' => 'fi-rr-shop',
        'users'       => 'fi-rr-users-gear',
        'roles'       => 'fi-rr-shield-check',
    ];

    $renderIcon = function (string $icon) use ($iconMap): string {
        $class = $iconMap[$icon] ?? 'fi-rr-menu-burger';
        return '<i class="fi ' . $class . ' text-[18px] leading-none"></i>';
    };
@endphp

@foreach($mainLinks as $link)
    <a href="{{ route($link['route']) }}" wire:navigate title="{{ $link['label'] }}" class="{{ $menuItemClass }} {{ request()->routeIs($link['match']) ? $menuActiveClass : $menuInactiveClass }}">
        @if($withCollapse)
            <span class="inline-flex h-5 w-5 items-center justify-center shrink-0">
                {!! $renderIcon($link['icon']) !!}
            </span>
            <span x-show="sidebarOpen" x-transition>{{ $link['label'] }}</span>
        @else
            {{ $link['label'] }}
        @endif
    </a>
@endforeach

@role('gerant')
    @if($withCollapse)
        <div class="{{ $menuSectionClass }}" x-show="sidebarOpen" x-transition>
            <p class="px-3 text-xs font-semibold uppercase tracking-wider text-slate-500">الإعدادات</p>
        </div>
    @else
        <div class="{{ $menuSectionClass }}">الإعدادات</div>
    @endif
    @foreach($parametrageLinks as $link)
        <a href="{{ route($link['route']) }}" wire:navigate title="{{ $link['label'] }}" class="{{ $menuItemClass }} {{ request()->routeIs($link['match']) ? $menuActiveClass : $menuInactiveClass }}">
            @if($withCollapse)
                <span class="inline-flex h-5 w-5 items-center justify-center shrink-0">
                    {!! $renderIcon($link['icon']) !!}
                </span>
                <span x-show="sidebarOpen" x-transition>{{ $link['label'] }}</span>
            @else
                {{ $link['label'] }}
            @endif
        </a>
    @endforeach

    @if($withCollapse)
        <div class="{{ $menuSectionClass }}" x-show="sidebarOpen" x-transition>
            <p class="px-3 text-xs font-semibold uppercase tracking-wider text-slate-500">الإدارة</p>
        </div>
    @else
        <div class="{{ $menuSectionClass }}">الإدارة</div>
    @endif
    @foreach($adminLinks as $link)
        <a href="{{ route($link['route']) }}" wire:navigate title="{{ $link['label'] }}" class="{{ $menuItemClass }} {{ request()->routeIs($link['match']) ? $menuActiveClass : $menuInactiveClass }}">
            @if($withCollapse)
                <span class="inline-flex h-5 w-5 items-center justify-center shrink-0">
                    {!! $renderIcon($link['icon']) !!}
                </span>
                <span x-show="sidebarOpen" x-transition>{{ $link['label'] }}</span>
            @else
                {{ $link['label'] }}
            @endif
        </a>
    @endforeach
@endrole
