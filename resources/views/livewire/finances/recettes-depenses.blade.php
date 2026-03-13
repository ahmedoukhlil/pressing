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
            <div class="flex gap-1 rounded-xl bg-slate-100 p-1 w-fit">
                <button wire:click="$set('groupePar', 'jour')"
                        class="px-4 py-1.5 rounded-lg text-sm font-medium transition {{ $groupePar === 'jour' ? 'bg-white shadow text-slate-800' : 'text-slate-500 hover:text-slate-700' }}">
                    يومي
                </button>
                <button wire:click="$set('groupePar', 'mois')"
                        class="px-4 py-1.5 rounded-lg text-sm font-medium transition {{ $groupePar === 'mois' ? 'bg-white shadow text-slate-800' : 'text-slate-500 hover:text-slate-700' }}">
                    شهري
                </button>
                <button wire:click="$set('groupePar', 'annee')"
                        class="px-4 py-1.5 rounded-lg text-sm font-medium transition {{ $groupePar === 'annee' ? 'bg-white shadow text-slate-800' : 'text-slate-500 hover:text-slate-700' }}">
                    سنوي
                </button>
            </div>

            <button wire:click="revenirPeriodeCourante" class="btn-secondary text-xs">
                <i class="fi fi-rr-time-past mr-1"></i> العودة للفترة الحالية
            </button>
        </div>

        <div class="flex flex-wrap items-center gap-2">
            @if($groupePar === 'jour')
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

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
        <div class="card card-body space-y-1">
            <div class="text-xs text-slate-500 flex items-center gap-2">
                <i class="fi fi-rr-money-bill-wave text-emerald-600"></i> إجمالي الإيرادات
            </div>
            <div class="text-2xl font-bold text-emerald-600 num-ltr">{{ number_format($this->total_recettes, 2, ',', ' ') }} MRU</div>
        </div>
        <div class="card card-body space-y-1">
            <div class="text-xs text-slate-500 flex items-center gap-2">
                <i class="fi fi-rr-money text-red-500"></i> إجمالي المصروفات
            </div>
            <div class="text-2xl font-bold text-red-500 num-ltr">{{ number_format($this->total_depenses, 2, ',', ' ') }} MRU</div>
        </div>
        <div class="card card-body space-y-1">
            <div class="text-xs text-slate-500 flex items-center gap-2">
                <i class="fi fi-rr-chart-line-up {{ $this->benefice_net >= 0 ? 'text-blue-600' : 'text-orange-500' }}"></i> صافي الربح
            </div>
            <div class="text-2xl font-bold num-ltr {{ $this->benefice_net >= 0 ? 'text-blue-600' : 'text-orange-500' }}">
                {{ number_format($this->benefice_net, 2, ',', ' ') }} MRU
            </div>
        </div>
    </div>

    {{-- Tableau --}}
    <div class="table-wrap">
        <table class="table-base w-full table-fixed text-sm">
            <colgroup>
                <col class="w-[21%]">
                <col class="w-[16%]">
                <col class="w-[16%]">
                <col class="w-[16%]">
                <col class="w-[16%]">
                <col class="w-[15%]">
            </colgroup>
            <thead class="table-head">
                <tr class="border-b border-slate-200">
                    <th class="table-th !text-right border-slate-200 border-l whitespace-nowrap text-[11px]">الفترة</th>
                    <th class="table-th !text-left border-slate-200 border-l whitespace-nowrap text-[11px]">الإيرادات (MRU)</th>
                    <th class="table-th !text-left border-slate-200 border-l whitespace-nowrap text-[11px]">المصروفات (MRU)</th>
                    <th class="table-th !text-left border-slate-200 border-l whitespace-nowrap text-[11px]">الصافي (MRU)</th>
                    <th class="table-th !text-center border-slate-200 border-l whitespace-nowrap text-[11px]">المقارنة</th>
                    <th class="table-th !text-center whitespace-nowrap text-[11px]">الحالة</th>
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
                        <td colspan="6" class="table-td py-10">
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
                        <td class="table-td !text-center border-slate-200 border-l text-xs text-slate-500 whitespace-nowrap">—</td>
                        <td class="table-td !text-center text-xs text-slate-500 whitespace-nowrap">—</td>
                    </tr>
                </tfoot>
            @endif
        </table>
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
        <p class="text-xs text-slate-500">يعرض هذا الجدول كل عملية على حدة حسب الفترة المختارة أعلاه (يومي / شهري / سنوي).</p>

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
