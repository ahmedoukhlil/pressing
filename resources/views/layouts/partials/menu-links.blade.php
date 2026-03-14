@php
    $mainLinks = [
        ['route' => 'dashboard', 'match' => 'dashboard', 'label' => 'لوحة التحكم', 'icon' => 'dashboard', 'permission' => 'view.dashboard'],
        ['route' => 'pos', 'match' => 'pos', 'label' => 'إيداع الملابس', 'icon' => 'depot', 'permission' => 'view.pos'],
        ['route' => 'recherche', 'match' => 'recherche', 'label' => 'الطلبات', 'icon' => 'commandes', 'permission' => 'view.recherche'],
        ['route' => 'clients.index', 'match' => 'clients.*', 'label' => 'الزبناء', 'icon' => 'clients', 'permission' => 'view.clients.index'],
        ['route' => 'depenses.index', 'match' => 'depenses.*', 'label' => 'المصروفات', 'icon' => 'depenses', 'permission' => 'view.depenses.index'],
        ['route' => 'finances.recettes-depenses', 'match' => 'finances.*', 'label' => 'الإيرادات والمصروفات', 'icon' => 'finances', 'permission' => 'view.finances.recettes-depenses'],
    ];

    $parametrageLinks = [
        ['route' => 'services.index', 'match' => 'services.*', 'label' => 'الخدمات', 'icon' => 'services', 'permission' => 'view.services.index'],
        ['route' => 'parametrage.fidelite', 'match' => 'parametrage.fidelite', 'label' => 'نظام نقاط الولاء', 'icon' => 'settings', 'permission' => 'view.parametrage.parametres-generaux'],
        ['route' => 'parametrage.stock-consommables.index', 'match' => 'parametrage.stock-consommables.*', 'label' => 'المخزون', 'icon' => 'stock', 'permission' => 'view.parametrage.stock-consommables.index'],
        ['route' => 'parametrage.employes.index', 'match' => 'parametrage.employes.*', 'label' => 'الموظفون', 'icon' => 'employes', 'permission' => 'view.parametrage.employes.index'],
        ['route' => 'parametrage.modes-paiement.index', 'match' => 'parametrage.modes-paiement.*', 'label' => 'طرق الدفع', 'icon' => 'modes', 'permission' => 'view.parametrage.modes-paiement.index'],
        ['route' => 'parametrage.fournisseurs.index', 'match' => 'parametrage.fournisseurs.*', 'label' => 'الموردون', 'icon' => 'fournisseurs', 'permission' => 'view.parametrage.fournisseurs.index'],
        ['route' => 'parametrage.types-depenses.index', 'match' => 'parametrage.types-depenses.*', 'label' => 'أنواع المصروفات', 'icon' => 'types', 'permission' => 'view.parametrage.types-depenses.index'],
    ];

    $adminLinks = [
        ['route' => 'admin.succursales.index', 'match' => 'admin.succursales.*', 'label' => 'الفروع', 'icon' => 'succursales', 'permission' => 'view.admin.succursales.index'],
        ['route' => 'admin.users.index', 'match' => 'admin.users.*', 'label' => 'المستخدمون', 'icon' => 'users', 'permission' => 'view.admin.users.index'],
    ];

    $iconMap = [
        'dashboard'   => 'fi-rr-dashboard',
        'depot'       => 'fi-rr-clothes-hanger',
        'commandes'   => 'fi-rr-clipboard-list',
        'clients'     => 'fi-rr-users',
        'depenses'    => 'fi-rr-money-bill-wave',
        'finances'    => 'fi-rr-chart-line-up',
        'services'    => 'fi-rr-star',
        'settings'    => 'fi-rr-settings',
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
    @continue(!auth()->user()?->can($link['permission']))
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

@php
    $visibleParametrageLinks = array_filter($parametrageLinks, fn (array $link): bool => auth()->user()?->can($link['permission']));
    $visibleAdminLinks = array_filter($adminLinks, fn (array $link): bool => auth()->user()?->can($link['permission']));
@endphp

@if(count($visibleParametrageLinks) > 0)
    @if($withCollapse)
        <div class="{{ $menuSectionClass }}" x-show="sidebarOpen" x-transition>
            <p class="px-3 text-xs font-semibold uppercase tracking-wider text-slate-500">الإعدادات</p>
        </div>
    @else
        <div class="{{ $menuSectionClass }}">الإعدادات</div>
    @endif
    @foreach($visibleParametrageLinks as $link)
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
@endif

@if(count($visibleAdminLinks) > 0)
    @if($withCollapse)
        <div class="{{ $menuSectionClass }}" x-show="sidebarOpen" x-transition>
            <p class="px-3 text-xs font-semibold uppercase tracking-wider text-slate-500">الإدارة</p>
        </div>
    @else
        <div class="{{ $menuSectionClass }}">الإدارة</div>
    @endif
    @foreach($visibleAdminLinks as $link)
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
@endif
