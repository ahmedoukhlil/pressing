<div class="page-container space-y-6">
    @php
        $exportParams = [
            'groupe_par' => $groupePar,
            'annee' => $annee,
            'mois' => $mois,
        ];
    @endphp

    <div class="page-header">
        <div>
            <h1 class="page-title">الإيرادات والمصروفات</h1>
            <p class="page-subtitle">واجهة مبسطة لمتابعة الأموال الداخلة (إيرادات) والأموال الخارجة (مصروفات).</p>
        </div>
        <div class="flex flex-wrap items-center gap-2">
            <a href="{{ route('exports.finances.details.excel', $exportParams) }}" class="btn-secondary text-xs">
                <i class="fi fi-rr-file-excel mr-1"></i> تصدير التفاصيل Excel
            </a>
            <a href="{{ route('exports.finances.details.pdf', $exportParams) }}" class="btn-secondary text-xs">
                <i class="fi fi-rr-file-pdf mr-1"></i> تصدير التفاصيل PDF
            </a>
        </div>
    </div>

    <div class="card card-body space-y-4">
        <div class="flex flex-wrap items-center gap-2 justify-between">
            <div class="flex flex-wrap gap-1 rounded-xl bg-slate-100 p-1 w-fit">
                <button type="button" wire:click="$set('groupePar', 'jour')"
                        class="px-3 sm:px-4 py-1.5 rounded-lg text-sm font-medium transition {{ $groupePar === 'jour' ? 'bg-white shadow text-slate-800' : 'text-slate-500 hover:text-slate-700' }}">
                    يومي
                </button>
                <button type="button" wire:click="$set('groupePar', 'semaine')"
                        class="px-3 sm:px-4 py-1.5 rounded-lg text-sm font-medium transition {{ $groupePar === 'semaine' ? 'bg-white shadow text-slate-800' : 'text-slate-500 hover:text-slate-700' }}">
                    أسبوعي
                </button>
                <button type="button" wire:click="$set('groupePar', 'mois')"
                        class="px-3 sm:px-4 py-1.5 rounded-lg text-sm font-medium transition {{ $groupePar === 'mois' ? 'bg-white shadow text-slate-800' : 'text-slate-500 hover:text-slate-700' }}">
                    شهري
                </button>
                <button type="button" wire:click="$set('groupePar', 'annee')"
                        class="px-3 sm:px-4 py-1.5 rounded-lg text-sm font-medium transition {{ $groupePar === 'annee' ? 'bg-white shadow text-slate-800' : 'text-slate-500 hover:text-slate-700' }}">
                    سنوي
                </button>
            </div>

            <button wire:click="revenirPeriodeCourante" class="btn-secondary text-xs">
                <i class="fi fi-rr-time-past mr-1"></i> العودة للفترة الحالية
            </button>
        </div>

        <div class="flex flex-wrap items-center gap-2">
            @if(in_array($groupePar, ['jour', 'semaine'], true))
                <select wire:model.live="mois" class="form-field w-auto min-w-[150px]">
                    @foreach(['يناير','فبراير','مارس','أبريل','مايو','يونيو','يوليو','أغسطس','سبتمبر','أكتوبر','نوفمبر','ديسمبر'] as $i => $label)
                        <option value="{{ $i + 1 }}">{{ $label }}</option>
                    @endforeach
                </select>
                <select wire:model.live="annee" class="form-field w-auto min-w-[110px]">
                    @foreach($this->anneesDisponibles as $a)
                        <option value="{{ $a }}">{{ $a }}</option>
                    @endforeach
                </select>
            @elseif($groupePar === 'mois')
                <select wire:model.live="annee" class="form-field w-auto min-w-[110px]">
                    @foreach($this->anneesDisponibles as $a)
                        <option value="{{ $a }}">{{ $a }}</option>
                    @endforeach
                </select>
            @endif

            <span class="inline-flex items-center rounded-full bg-blue-50 text-blue-700 px-3 py-1 text-xs">
                الفترة: {{ $this->periode_selectionnee_label }}
            </span>
            <span class="inline-flex items-center rounded-full bg-slate-100 text-slate-600 px-3 py-1 text-xs">
                عدد الفترات: {{ $this->nombre_periodes }}
            </span>
        </div>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4">
        <div class="card card-body space-y-1">
            <div class="text-xs text-slate-500 flex items-center gap-2">
                <i class="fi fi-rr-money-bill-wave text-emerald-600"></i> إجمالي الإيرادات
            </div>
            <div class="text-2xl font-bold text-emerald-600 num-ltr">{{ number_format($this->total_recettes, 2, ',', ' ') }} MRU</div>
            <p class="text-[10px] text-slate-400">ضمن الفترة المعروضة أعلاه</p>
        </div>
        <div class="card card-body space-y-1">
            <div class="text-xs text-slate-500 flex items-center gap-2">
                <i class="fi fi-rr-money text-red-500"></i> إجمالي المصروفات
            </div>
            <div class="text-2xl font-bold text-red-500 num-ltr">{{ number_format($this->total_depenses, 2, ',', ' ') }} MRU</div>
            <p class="text-[10px] text-slate-400">ضمن الفترة المعروضة أعلاه</p>
        </div>
        <div class="card card-body space-y-1">
            <div class="text-xs text-slate-500 flex items-center gap-2">
                <i class="fi fi-rr-chart-line-up {{ $this->benefice_net >= 0 ? 'text-blue-600' : 'text-orange-500' }}"></i> صافي الربح
            </div>
            <div class="text-2xl font-bold num-ltr {{ $this->benefice_net >= 0 ? 'text-blue-600' : 'text-orange-500' }}">
                {{ number_format($this->benefice_net, 2, ',', ' ') }} MRU
            </div>
            <p class="text-[10px] text-slate-400">إيرادات − مصروفات (نفس الجدول)</p>
        </div>
        <div class="card card-body space-y-1 border border-violet-100 bg-violet-50/50 shadow-sm ring-1 ring-violet-100/70">
            <div class="text-xs text-violet-900/85 flex items-center gap-2 font-medium">
                <i class="fi fi-rr-receipt text-violet-600"></i> مستحقات غير محصّلة
            </div>
            <div class="text-2xl font-bold text-violet-900 num-ltr">{{ number_format($this->montants_non_percus, 2, ',', ' ') }} MRU</div>
            <p class="text-[10px] text-violet-800/90 leading-snug">
                @if($groupePar === 'annee')
                    المتبقي على كل الطلبات غير الملغاة (كل تواريخ الإيداع).
                @elseif($groupePar === 'mois')
                    طلبات تاريخ إيداعها ضمن سنة العرض وعليها متبقٍّ غير محصّل.
                @else
                    طلبات تاريخ إيداعها ضمن شهر العرض وعليها متبقٍّ غير محصّل.
                @endif
                · <span class="num-ltr font-bold">{{ $this->nombre_commandes_non_percues }}</span> طلبًا
            </p>
            <a href="{{ route('recherche') }}" wire:navigate class="inline-block text-[11px] font-semibold text-violet-800 hover:text-violet-950 underline-offset-2 hover:underline">
                متابعة التحصيل
            </a>
        </div>
    </div>

    {{-- Ventilation recettes par mode de paiement --}}
    <div class="card card-body">
        <div class="flex items-center justify-between mb-4">
            <div>
                <h2 class="text-sm font-semibold text-slate-800">الإيرادات حسب طريقة الدفع</h2>
                <p class="text-xs text-slate-400 mt-0.5">{{ $this->periode_selectionnee_label }} · انقر على بطاقة لعرض التفاصيل</p>
            </div>
            <div class="text-right">
                <p class="text-xs text-slate-500">إجمالي الإيرادات</p>
                <p class="text-base font-bold text-emerald-700 num-ltr">{{ number_format($this->total_recettes, 2, ',', ' ') }} MRU</p>
            </div>
        </div>
        @if($this->recettes_par_mode->isEmpty())
            <p class="text-sm text-slate-400 text-center py-4">لا توجد إيرادات في هذه الفترة.</p>
        @else
            <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                @foreach($this->recettes_par_mode as $item)
                    @php
                        $pct = $this->total_recettes > 0 ? round(($item['total'] / $this->total_recettes) * 100, 1) : 0;
                    @endphp
                    <button type="button"
                        wire:click="ouvrirDetailMode('{{ $item['code'] }}')"
                        class="group text-right rounded-xl border border-slate-200 bg-white hover:border-emerald-400 hover:shadow-md hover:bg-emerald-50/30 transition-all p-4 space-y-2">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-semibold text-slate-500 group-hover:text-emerald-700 transition">{{ $item['libelle'] }}</span>
                            <span class="text-xs text-slate-400 num-ltr">{{ $pct }}%</span>
                        </div>
                        <p class="text-xl font-bold text-slate-800 num-ltr tabular-nums">{{ number_format($item['total'], 2, ',', ' ') }}</p>
                        <p class="text-[10px] text-slate-400">MRU</p>
                        <div class="h-1.5 w-full rounded-full bg-slate-100 overflow-hidden">
                            <div class="h-full rounded-full bg-emerald-500 transition-all" style="width: {{ $pct }}%"></div>
                        </div>
                        <p class="text-[10px] text-emerald-600 opacity-0 group-hover:opacity-100 transition">عرض التفاصيل ←</p>
                    </button>
                @endforeach
            </div>
        @endif
    </div>

    {{-- Modale détail mode de paiement --}}
    @if($afficherDetailMode && $modeSelectionne)
        @php
            $modeLabel = $this->recettes_par_mode->firstWhere('code', $modeSelectionne)['libelle'] ?? $modeSelectionne;
            $ops = $this->operations_par_mode;
            $totalRecetteMode = $ops->where('type', 'recette')->sum('montant');
            $totalDepenseMode = $ops->where('type', 'depense')->sum('montant');
        @endphp
        <div class="modal-overlay flex items-center justify-center p-4">
            <div class="modal-panel max-w-2xl w-full p-5 space-y-4">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-base font-semibold text-slate-900">تفاصيل طريقة الدفع: {{ $modeLabel }}</h3>
                        <p class="text-xs text-slate-500 mt-0.5">{{ $this->periode_selectionnee_label }}</p>
                    </div>
                    <button wire:click="fermerDetailMode" class="btn-secondary">إغلاق</button>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div class="rounded-lg bg-emerald-50 border border-emerald-200 px-3 py-2">
                        <p class="text-[11px] text-emerald-700">إجمالي الإيرادات</p>
                        <p class="font-bold text-emerald-800 num-ltr">{{ number_format($totalRecetteMode, 2, ',', ' ') }} MRU</p>
                    </div>
                    <div class="rounded-lg bg-red-50 border border-red-200 px-3 py-2">
                        <p class="text-[11px] text-red-700">إجمالي المصروفات</p>
                        <p class="font-bold text-red-800 num-ltr">{{ number_format($totalDepenseMode, 2, ',', ' ') }} MRU</p>
                    </div>
                </div>

                <div class="table-wrap max-h-96 overflow-y-auto">
                    <table class="table-base w-full text-sm">
                        <thead class="table-head sticky top-0">
                            <tr>
                                <th class="table-th !text-right border-l border-slate-200 text-[11px]">التاريخ</th>
                                <th class="table-th !text-center border-l border-slate-200 text-[11px]">النوع</th>
                                <th class="table-th !text-right border-l border-slate-200 text-[11px]">البيان</th>
                                <th class="table-th !text-left text-[11px]">المبلغ (MRU)</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse($ops as $op)
                                <tr class="hover:bg-slate-50">
                                    <td class="table-td !text-right border-l border-slate-100 whitespace-nowrap text-xs">{{ $op['date'] }}</td>
                                    <td class="table-td !text-center border-l border-slate-100 whitespace-nowrap">
                                        @if($op['type'] === 'recette')
                                            <span class="inline-flex items-center rounded-full bg-emerald-50 px-2 py-0.5 text-xs text-emerald-700">إيراد</span>
                                        @else
                                            <span class="inline-flex items-center rounded-full bg-red-50 px-2 py-0.5 text-xs text-red-600">مصروف</span>
                                        @endif
                                    </td>
                                    <td class="table-td !text-right border-l border-slate-100 max-w-[200px]">
                                        <div class="truncate text-xs" title="{{ $op['designation'] }}">{{ $op['designation'] }}</div>
                                    </td>
                                    <td class="table-td !text-left whitespace-nowrap num-ltr tabular-nums {{ $op['type'] === 'recette' ? 'text-emerald-700' : 'text-red-600' }}">
                                        {{ number_format($op['montant'], 2, ',', ' ') }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="table-td text-center text-slate-400 py-6">لا توجد عمليات.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endif

    {{-- Tableau --}}
    <div class="table-wrap">
        <table class="table-base w-full table-fixed text-sm">
            <colgroup>
                <col class="w-[17%]">
                <col class="w-[12%]">
                <col class="w-[12%]">
                <col class="w-[12%]">
                <col class="w-[13%]">
                <col class="w-[14%]">
                <col class="w-[10%]">
            </colgroup>
            <thead class="table-head">
                <tr class="border-b border-slate-200">
                    <th class="table-th !text-right border-slate-200 border-l whitespace-nowrap text-[11px]">الفترة</th>
                    <th class="table-th !text-left border-slate-200 border-l whitespace-nowrap text-[11px]">الإيرادات</th>
                    <th class="table-th !text-left border-slate-200 border-l whitespace-nowrap text-[11px]">المصروفات</th>
                    <th class="table-th !text-left border-slate-200 border-l whitespace-nowrap text-[11px]">الصافي</th>
                    <th class="table-th !text-left border-slate-200 border-l whitespace-nowrap text-[11px]">مستحقات</th>
                    <th class="table-th !text-center border-slate-200 border-l whitespace-nowrap text-[11px]">مقارنة</th>
                    <th class="table-th !text-center whitespace-nowrap text-[11px]">حالة</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($this->lignes as $ligne)
                    <tr class="hover:bg-slate-50 transition">
                        <td class="table-td !text-right font-medium border-slate-100 border-l whitespace-nowrap">{{ $ligne['label'] }}</td>
                        <td class="table-td !text-left text-emerald-700 font-medium border-slate-100 border-l whitespace-nowrap">
                            <span class="num-ltr tabular-nums">
                                {{ $ligne['recettes'] > 0 ? number_format($ligne['recettes'], 2, ',', ' ') : '-' }}
                            </span>
                        </td>
                        <td class="table-td !text-left text-red-600 font-medium border-slate-100 border-l whitespace-nowrap">
                            <span class="num-ltr tabular-nums">
                                {{ $ligne['depenses'] > 0 ? number_format($ligne['depenses'], 2, ',', ' ') : '-' }}
                            </span>
                        </td>
                        <td class="table-td !text-left font-semibold border-slate-100 border-l whitespace-nowrap {{ $ligne['net'] >= 0 ? 'text-blue-700' : 'text-orange-600' }}">
                            <span class="num-ltr tabular-nums">
                                {{ number_format($ligne['net'], 2, ',', ' ') }}
                            </span>
                        </td>
                        <td class="table-td !text-left border-slate-100 border-l whitespace-nowrap text-violet-800 font-medium">
                            <span class="num-ltr tabular-nums">
                                {{ ($ligne['impayes'] ?? 0) > 0 ? number_format((float) $ligne['impayes'], 2, ',', ' ') : '-' }}
                            </span>
                        </td>
                        <td class="table-td !text-center border-slate-100 border-l">
                            @if((float) $ligne['recettes'] > 0 || (float) $ligne['depenses'] > 0)
                                @php
                                    $max = max((float) $ligne['recettes'], (float) $ligne['depenses'], 1);
                                    $pRecette = round(((float) $ligne['recettes'] / $max) * 100, 1);
                                    $pDepense = round(((float) $ligne['depenses'] / $max) * 100, 1);
                                @endphp
                                <div class="space-y-1 w-24 mx-auto">
                                    <div class="h-1.5 rounded bg-emerald-100 overflow-hidden">
                                        <div class="h-full bg-emerald-500" style="width: {{ $pRecette }}%"></div>
                                    </div>
                                    <div class="h-1.5 rounded bg-red-100 overflow-hidden">
                                        <div class="h-full bg-red-500" style="width: {{ $pDepense }}%"></div>
                                    </div>
                                </div>
                            @else
                                <span class="text-slate-300">—</span>
                            @endif
                        </td>
                        <td class="table-td !text-center whitespace-nowrap">
                            @if($ligne['net'] > 0)
                                <span class="inline-flex items-center gap-1 rounded-full bg-emerald-50 px-2 py-0.5 text-xs text-emerald-700">
                                    <i class="fi fi-rr-arrow-trend-up text-[11px]"></i> ربح
                                </span>
                            @elseif($ligne['net'] < 0)
                                <span class="inline-flex items-center gap-1 rounded-full bg-red-50 px-2 py-0.5 text-xs text-red-600">
                                    <i class="fi fi-rr-arrow-trend-down text-[11px]"></i> خسارة
                                </span>
                            @else
                                <span class="inline-flex items-center rounded-full bg-slate-100 px-2 py-0.5 text-xs text-slate-500">—</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="table-td py-10">
                            <div class="flex flex-col items-center justify-center text-slate-400 gap-2">
                                <i class="fi fi-rr-folder-open text-3xl"></i>
                                <p class="text-sm">لا توجد بيانات للفترة المحددة.</p>
                                <p class="text-xs">غيّر الفترة أو عد للفترة الحالية لعرض النتائج.</p>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
            @if($this->lignes->isNotEmpty())
                <tfoot class="bg-slate-50 border-t-2 border-slate-200">
                    <tr>
                        <td class="table-td !text-right font-bold text-slate-700 border-slate-200 border-l whitespace-nowrap">المجموع</td>
                        <td class="table-td !text-left font-bold text-emerald-700 border-slate-200 border-l whitespace-nowrap">
                            <span class="num-ltr tabular-nums">{{ number_format($this->total_recettes, 2, ',', ' ') }}</span>
                        </td>
                        <td class="table-td !text-left font-bold text-red-600 border-slate-200 border-l whitespace-nowrap">
                            <span class="num-ltr tabular-nums">{{ number_format($this->total_depenses, 2, ',', ' ') }}</span>
                        </td>
                        <td class="table-td !text-left font-bold border-slate-200 border-l whitespace-nowrap {{ $this->benefice_net >= 0 ? 'text-blue-700' : 'text-orange-600' }}">
                            <span class="num-ltr tabular-nums">{{ number_format($this->benefice_net, 2, ',', ' ') }}</span>
                        </td>
                        <td class="table-td !text-left font-bold border-slate-200 border-l whitespace-nowrap text-violet-900">
                            <span class="num-ltr tabular-nums">{{ number_format($this->total_impayes_lignes, 2, ',', ' ') }}</span>
                        </td>
                        <td class="table-td !text-center border-slate-200 border-l text-xs text-slate-500 whitespace-nowrap">—</td>
                        <td class="table-td !text-center text-xs text-slate-500 whitespace-nowrap">—</td>
                    </tr>
                </tfoot>
            @endif
        </table>
        @if($groupePar === 'annee')
            <p class="mt-2 text-[10px] text-slate-500 px-2">
                في العرض السنوي، قد يختلف مجموع عمود «مستحقات» عن بطاقة المستحقات إن وُجدت طلبات قديمة غير ظاهرة في الجدول (سنة بلا إيرادات/مصروفات مسجّلة).
            </p>
        @endif
    </div>

    <div class="card card-body space-y-3">
        <div class="flex flex-wrap items-center justify-between gap-2">
            <h2 class="text-base font-semibold text-slate-800">كشف تفصيلي للعمليات (إيرادات/مصروفات)</h2>
            <div class="flex flex-wrap items-center gap-2">
                <span class="text-xs text-slate-500">عدد العمليات: {{ $this->operations->count() }}</span>
                <a href="{{ route('exports.finances.details.excel', $exportParams) }}" class="btn-secondary !px-2.5 !py-1.5 !text-xs">
                    <i class="fi fi-rr-file-excel mr-1"></i> Excel
                </a>
                <a href="{{ route('exports.finances.details.pdf', $exportParams) }}" class="btn-secondary !px-2.5 !py-1.5 !text-xs">
                    <i class="fi fi-rr-file-pdf mr-1"></i> PDF
                </a>
            </div>
        </div>
        <p class="text-xs text-slate-500">يعرض هذا الجدول كل عملية على حدة حسب الفترة المختارة أعلاه (يومي / أسبوعي / شهري / سنوي). عمود «مستحقات» يخصّص المتبقي غير المحصّل حسب تاريخ إيداع الطلب داخل كل سطر فترة.</p>

        <div class="table-wrap">
            <table class="table-base w-full table-fixed text-sm">
                <colgroup>
                    <col class="w-[16%]">
                    <col class="w-[12%]">
                    <col class="w-[34%]">
                    <col class="w-[14%]">
                    <col class="w-[12%]">
                    <col class="w-[12%]">
                </colgroup>
                <thead class="table-head">
                    <tr class="border-b border-slate-200">
                        <th class="table-th !text-right border-slate-200 border-l whitespace-nowrap text-[11px]">التاريخ</th>
                        <th class="table-th !text-center border-slate-200 border-l whitespace-nowrap text-[11px]">النوع</th>
                        <th class="table-th !text-right border-slate-200 border-l whitespace-nowrap text-[11px]">البيان</th>
                        <th class="table-th !text-center border-slate-200 border-l whitespace-nowrap text-[11px]">طريقة الدفع</th>
                        <th class="table-th !text-left border-slate-200 border-l whitespace-nowrap text-[11px]">إيراد (MRU)</th>
                        <th class="table-th !text-left whitespace-nowrap text-[11px]">مصروف (MRU)</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($this->operations as $operation)
                        <tr class="hover:bg-slate-50 transition">
                            <td class="table-td !text-right border-slate-100 border-l whitespace-nowrap">
                                {{ $operation['date']->format('Y-m-d H:i') }}
                            </td>
                            <td class="table-td !text-center border-slate-100 border-l whitespace-nowrap">
                                @if($operation['type'] === 'recette')
                                    <span class="inline-flex items-center rounded-full bg-emerald-50 px-2 py-0.5 text-xs text-emerald-700">إيراد</span>
                                @else
                                    <span class="inline-flex items-center rounded-full bg-red-50 px-2 py-0.5 text-xs text-red-600">مصروف</span>
                                @endif
                            </td>
                            <td class="table-td !text-right border-slate-100 border-l">
                                <div class="truncate" title="{{ $operation['designation'] }}">{{ $operation['designation'] }}</div>
                            </td>
                            <td class="table-td !text-center border-slate-100 border-l whitespace-nowrap text-xs text-slate-600">
                                {{ $operation['mode_paiement'] }}
                            </td>
                            <td class="table-td !text-left border-slate-100 border-l whitespace-nowrap text-emerald-700">
                                <span class="num-ltr tabular-nums">
                                    {{ $operation['recette'] > 0 ? number_format($operation['recette'], 2, ',', ' ') : '-' }}
                                </span>
                            </td>
                            <td class="table-td !text-left whitespace-nowrap text-red-600">
                                <span class="num-ltr tabular-nums">
                                    {{ $operation['depense'] > 0 ? number_format($operation['depense'], 2, ',', ' ') : '-' }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="table-td py-8 text-center text-slate-400">
                                لا توجد عمليات في هذه الفترة.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>
