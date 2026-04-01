<div class="page-container space-y-6" x-data="{ simpleMode: true }">
    @php
        $commandesTotal = max(1, (int) $stats['commandes_total']);
        $tauxEnCours = round(((int) $stats['en_cours'] / $commandesTotal) * 100);
        $tauxPret = round(((int) $stats['pret'] / $commandesTotal) * 100);
    @endphp

    <div class="page-header">
        <div>
            <h1 class="page-title">لوحة تحكم {{ config('app.name') }}</h1>
            <p class="page-subtitle">ملخص حي لأداء المغسلة اليوم مع مؤشرات واضحة لاتخاذ القرار بسرعة.</p>
        </div>
        <div class="flex flex-wrap gap-2">
            <button type="button" class="btn-secondary" @click="simpleMode = !simpleMode">
                <span x-show="simpleMode">عرض التفاصيل</span>
                <span x-show="!simpleMode">عرض مختصر</span>
            </button>
            <a href="{{ route('pos') }}" wire:navigate class="btn-primary">إيداع جديد</a>
            <a href="{{ route('recherche') }}" wire:navigate class="btn-secondary">متابعة الطلبات</a>
        </div>
    </div>

    <div class="card card-body bg-gradient-to-l from-blue-600 to-indigo-600 text-white border-0">
        <div class="grid gap-4 md:grid-cols-3 items-center">
            <div class="md:col-span-2">
                <p class="text-sm text-blue-100">نظرة اليوم</p>
                <p class="text-2xl font-bold mt-1">حركة المغسلة الآن</p>
                <p class="text-sm text-blue-100 mt-2">
                    عدد طلبات اليوم <span class="num-ltr font-semibold text-white">{{ $stats['commandes_du_jour'] }}</span>
                    و مبيعات محصلة <span class="num-ltr font-semibold text-white">{{ number_format($stats['ca_jour'], 2, ',', ' ') }} MRU</span>
                </p>
            </div>
            <div class="rounded-xl bg-white/15 p-4 backdrop-blur-sm">
                <p class="text-xs text-blue-100">العملاء المسجلون</p>
                <p class="text-3xl font-bold mt-1"><span class="num-ltr">{{ $stats['clients'] }}</span></p>
                <p class="text-xs text-blue-100 mt-1">قاعدة زبناء نشطة</p>
            </div>
        </div>
    </div>

    <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3 2xl:grid-cols-5">
        <div class="card card-body">
            <p class="text-sm text-slate-500 flex items-center gap-2"><i class="fi fi-rr-box-open text-blue-600"></i> طلبات اليوم</p>
            <p class="text-2xl font-bold mt-1 text-slate-900"><span class="num-ltr">{{ $stats['commandes_du_jour'] }}</span></p>
            <p class="text-xs text-slate-500 mt-1">من إجمالي <span class="num-ltr">{{ $stats['commandes_total'] }}</span> طلب</p>
        </div>
        <div class="card card-body">
            <p class="text-sm text-slate-500 flex items-center gap-2"><i class="fi fi-rr-money-bill-wave text-emerald-600"></i> المبيعات المحصلة اليوم</p>
            <p class="text-2xl font-bold mt-1 text-emerald-700"><span class="num-ltr" dir="ltr">{{ number_format($stats['ca_jour'], 2, ',', ' ') }} MRU</span></p>
            <p class="text-xs text-slate-500 mt-1">مبالغ تم تحصيلها فعليًا</p>
        </div>
        <div class="card card-body border border-violet-100 bg-violet-50/40 shadow-sm ring-1 ring-violet-100/80">
            <p class="text-sm text-violet-900/80 flex items-center gap-2 font-medium"><i class="fi fi-rr-receipt text-violet-600"></i> مستحقات غير محصّلة</p>
            <p class="text-2xl font-bold mt-1 text-violet-800"><span class="num-ltr" dir="ltr">{{ number_format($stats['montants_factures_non_percus'], 2, ',', ' ') }} MRU</span></p>
            <p class="text-xs text-violet-700/90 mt-1">
                مجموع المتبقي على <span class="num-ltr font-semibold">{{ $stats['commandes_avec_reste'] }}</span> طلبًا (فوترة دون اكتمال الدفع)
            </p>
            <a href="{{ route('recherche') }}" wire:navigate class="mt-2 inline-block text-[11px] font-semibold text-violet-700 hover:text-violet-950 underline-offset-2 hover:underline">
                متابعة التحصيل ←
            </a>
        </div>
        <div class="card card-body">
            <p class="text-sm text-slate-500 flex items-center gap-2"><i class="fi fi-rr-hourglass-end text-amber-600"></i> قيد المعالجة</p>
            <p class="text-2xl font-bold mt-1 text-amber-700"><span class="num-ltr">{{ $stats['en_cours'] }}</span></p>
            <p class="text-xs text-slate-500 mt-1">تمثل {{ $tauxEnCours }}% من الطلبات</p>
        </div>
        <div class="card card-body">
            <p class="text-sm text-slate-500 flex items-center gap-2"><i class="fi fi-rr-badge-check text-cyan-700"></i> طلبات جاهزة للتسليم</p>
            <p class="text-2xl font-bold mt-1 text-cyan-700"><span class="num-ltr">{{ $stats['pret'] }}</span></p>
            <p class="text-xs text-slate-500 mt-1">تمثل {{ $tauxPret }}% من الطلبات</p>
        </div>
    </div>

    {{-- Recettes du mois ventilées par mode de paiement --}}
    <div class="card card-body">
        <div class="flex items-center justify-between mb-3">
            <div>
                <h2 class="card-title mb-0">إيرادات الشهر حسب طريقة الدفع</h2>
                <p class="text-xs text-slate-400 mt-0.5">{{ now()->translatedFormat('F Y') }}</p>
            </div>
            <div class="text-right">
                <p class="text-xs text-slate-500">الإجمالي</p>
                <p class="text-lg font-bold text-emerald-700 num-ltr">{{ number_format($totalRecettesMois, 2, ',', ' ') }} MRU</p>
            </div>
        </div>
        @if($recettesParMode->isEmpty())
            <p class="text-sm text-slate-400 text-center py-4">لا توجد إيرادات مسجلة هذا الشهر.</p>
        @else
            <div class="space-y-2.5">
                @foreach($recettesParMode as $item)
                    @php
                        $pct = $totalRecettesMois > 0 ? round(($item['total'] / $totalRecettesMois) * 100, 1) : 0;
                    @endphp
                    <div>
                        <div class="flex items-center justify-between text-sm mb-1">
                            <span class="font-medium text-slate-700">{{ $item['libelle'] }}</span>
                            <span class="num-ltr text-slate-600 tabular-nums">{{ number_format($item['total'], 2, ',', ' ') }} MRU <span class="text-xs text-slate-400">({{ $pct }}%)</span></span>
                        </div>
                        <div class="h-2 w-full rounded-full bg-slate-100 overflow-hidden">
                            <div class="h-full rounded-full bg-emerald-500 transition-all" style="width: {{ $pct }}%"></div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    <div class="grid md:grid-cols-3 gap-4" x-show="!simpleMode" x-transition>
        <div class="card card-body">
            <p class="text-sm text-slate-500">إجمالي الطلبات</p>
            <p class="text-xl font-semibold mt-1"><span class="num-ltr">{{ $stats['commandes_total'] }}</span></p>
            <div class="mt-3 h-2 w-full rounded-full bg-slate-100">
                <div class="h-2 rounded-full bg-indigo-500" style="width: 100%"></div>
            </div>
        </div>
        <div class="card card-body">
            <p class="text-sm text-slate-500">قيد المعالجة</p>
            <p class="text-xl font-semibold mt-1"><span class="num-ltr">{{ $stats['en_cours'] }}</span></p>
            <div class="mt-3 h-2 w-full rounded-full bg-slate-100">
                <div class="h-2 rounded-full bg-amber-500" style="width: {{ $tauxEnCours }}%"></div>
            </div>
        </div>
        <div class="card card-body">
            <p class="text-sm text-slate-500">جاهزة للتسليم</p>
            <p class="text-xl font-semibold mt-1"><span class="num-ltr">{{ $stats['pret'] }}</span></p>
            <div class="mt-3 h-2 w-full rounded-full bg-slate-100">
                <div class="h-2 rounded-full bg-cyan-500" style="width: {{ $tauxPret }}%"></div>
            </div>
        </div>
    </div>

    <div class="grid md:grid-cols-2 gap-4" x-show="!simpleMode" x-transition>
        <div class="card card-body">
            <p class="text-sm text-slate-500 flex items-center gap-2"><i class="fi fi-rr-money text-rose-700"></i> مصروفات الشهر</p>
            <p class="text-2xl font-bold mt-1 text-rose-700"><span class="num-ltr" dir="ltr">{{ number_format($stats['depenses_mois'], 2, ',', ' ') }} MRU</span></p>
            <p class="text-xs text-slate-500 mt-2">يساعدك هذا المؤشر على تتبع ضغط التكاليف خلال الشهر.</p>
        </div>
        <div class="card card-body">
            <p class="text-sm text-slate-500 flex items-center gap-2"><i class="fi fi-rr-scale text-indigo-700"></i> صافي اليوم (تحصيلي - مصروفات الشهر)</p>
            <p class="text-2xl font-bold mt-1 {{ ($stats['ca_jour'] - $stats['depenses_mois']) >= 0 ? 'text-emerald-700' : 'text-rose-700' }}">
                <span class="num-ltr" dir="ltr">{{ number_format($stats['ca_jour'] - $stats['depenses_mois'], 2, ',', ' ') }} MRU</span>
            </p>
            <p class="text-xs text-slate-500 mt-2">مؤشر سريع فقط للمراقبة (ليس ربحًا محاسبيًا نهائيًا).</p>
        </div>
    </div>

    <div class="grid lg:grid-cols-2 gap-4" x-show="!simpleMode" x-transition>
        <div class="card card-body">
            <div class="flex items-center justify-between mb-3">
                <h2 class="card-title mb-0">طلبات تقترب من 7 أيام</h2>
                <span class="status-badge status-warning"><span class="num-ltr">{{ $commandesProchesEcheance->count() }}</span></span>
            </div>

            @forelse($commandesProchesEcheance as $commande)
                <div class="flex items-center justify-between gap-2 py-2 border-b border-slate-100 last:border-b-0">
                    <div class="text-sm">
                        <div class="font-medium text-slate-900">{{ $commande->numero_commande }} - {{ $commande->client?->full_name }}</div>
                        <div class="text-slate-500">
                            منذ <span class="num-ltr">{{ $commande->jours_depuis_depot }}</span> أيام |
                            {{ optional($commande->date_depot)->format('d/m/Y') }}
                        </div>
                    </div>
                    <a href="tel:{{ $commande->client?->telephone }}" class="btn-secondary text-xs">اتصال</a>
                </div>
            @empty
                <div class="text-sm text-slate-500">لا توجد طلبات قريبة من الحد حاليا.</div>
            @endforelse
        </div>

        <div class="card card-body">
            <div class="flex items-center justify-between mb-3">
                <h2 class="card-title mb-0">طلبات تجاوزت 7 أيام</h2>
                <span class="status-badge status-danger"><span class="num-ltr">{{ $commandesHorsDelai->count() }}</span></span>
            </div>

            @forelse($commandesHorsDelai as $commande)
                <div class="flex items-center justify-between gap-2 py-2 border-b border-slate-100 last:border-b-0">
                    <div class="text-sm">
                        <div class="font-medium text-slate-900">{{ $commande->numero_commande }} - {{ $commande->client?->full_name }}</div>
                        <div class="text-slate-500">
                            منذ <span class="num-ltr">{{ $commande->jours_depuis_depot }}</span> أيام |
                            {{ optional($commande->date_depot)->format('d/m/Y') }}
                        </div>
                    </div>
                    <a href="tel:{{ $commande->client?->telephone }}" class="btn-secondary text-xs">اتصال</a>
                </div>
            @empty
                <div class="text-sm text-slate-500">لا توجد طلبات متجاوزة للحد حاليا.</div>
            @endforelse
        </div>
    </div>
</div>
