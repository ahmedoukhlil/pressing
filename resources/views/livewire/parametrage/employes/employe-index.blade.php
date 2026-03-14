<div class="page-container">
    <div class="page-header">
        <div>
            <h1 class="page-title">الموظفون</h1>
            <p class="page-subtitle">إدارة الموظفين مع تتبع مصاريف الرواتب والنقل لكل موظف.</p>
        </div>
        <a href="{{ route('parametrage.employes.create') }}" wire:navigate class="btn-primary">موظف جديد</a>
    </div>

    <div class="grid gap-2 sm:grid-cols-3 mb-3">
        <div class="card card-body !p-3">
            <p class="text-[11px] text-slate-500">عدد الموظفين</p>
            <p class="text-base font-bold text-slate-900 num-ltr">{{ number_format((int) $statsEmployes['count']) }}</p>
        </div>
        <div class="card card-body !p-3">
            <p class="text-[11px] text-slate-500">إجمالي مصاريف الرواتب</p>
            <p class="text-base font-bold text-emerald-700 num-ltr">{{ number_format((float) $statsEmployes['salaires'], 2, ',', ' ') }} MRU</p>
        </div>
        <div class="card card-body !p-3">
            <p class="text-[11px] text-slate-500">إجمالي مصاريف النقل</p>
            <p class="text-base font-bold text-indigo-700 num-ltr">{{ number_format((float) $statsEmployes['transport'], 2, ',', ' ') }} MRU</p>
        </div>
    </div>

    <div class="card card-body mb-4">
        <input wire:model.live.debounce.400ms="search" type="text" class="form-field md:max-w-md" placeholder="ابحث بالاسم أو الهاتف">
    </div>

    <div class="table-wrap">
        <table class="table-base">
            <thead class="table-head">
                <tr>
                    <th class="table-th">الموظف</th>
                    <th class="table-th">الوظيفة</th>
                    <th class="table-th">الراتب الإجمالي</th>
                    <th class="table-th">إجمالي الرواتب</th>
                    <th class="table-th">إجمالي النقل</th>
                    <th class="table-th text-right">الإجراءات</th>
                </tr>
            </thead>
            <tbody>
                @forelse($employes as $e)
                    <tr class="table-row">
                        <td class="table-td">{{ $e->full_name }}</td>
                        <td class="table-td">{{ $e->poste?->libelle ?? '-' }}</td>
                        <td class="table-td"><span class="num-ltr">{{ number_format((float) $e->salaire_brut, 2, ',', ' ') }} MRU</span></td>
                        <td class="table-td"><span class="num-ltr">{{ number_format((float) ($e->total_salaires ?? 0), 2, ',', ' ') }} MRU</span></td>
                        <td class="table-td"><span class="num-ltr">{{ number_format((float) ($e->total_transport ?? 0), 2, ',', ' ') }} MRU</span></td>
                        <td class="table-td text-right">
                            <div class="inline-flex items-center gap-2">
                                <button wire:click="ouvrirDepensesEmploye({{ $e->id }})" class="btn-ghost !px-2.5 !py-1.5 !text-xs text-slate-700">
                                    <i class="fi fi-rr-receipt mr-1"></i> التفاصيل
                                </button>
                                <a href="{{ route('parametrage.employes.edit', $e->id) }}" wire:navigate class="btn-ghost !px-2.5 !py-1.5 !text-xs text-blue-700">
                                    <i class="fi fi-rr-edit mr-1"></i> تعديل
                                </a>
                                <a href="{{ route('parametrage.employes.avances', $e->id) }}" wire:navigate class="btn-ghost !px-2.5 !py-1.5 !text-xs text-green-700">
                                    <i class="fi fi-rr-hand-holding-usd mr-1"></i> السلف
                                </a>
                                <a href="{{ route('parametrage.employes.paiement', $e->id) }}" wire:navigate class="btn-ghost !px-2.5 !py-1.5 !text-xs text-indigo-700">
                                    <i class="fi fi-rr-money-check-edit mr-1"></i> دفع الرواتب
                                </a>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="table-td text-center text-gray-500">لا يوجد موظفون.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-3">{{ $employes->links() }}</div>

    @if($afficherDepensesModal && $employeDetails)
        <div class="modal-overlay flex items-center justify-center p-4">
            <div class="modal-panel max-w-3xl p-4 space-y-3">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-base font-semibold text-slate-900">تفاصيل مصاريف الموظف</h3>
                        <p class="text-xs text-slate-500">{{ $employeDetails->full_name }}</p>
                    </div>
                    <button wire:click="fermerDepensesEmploye" class="btn-secondary">إغلاق</button>
                </div>

                <div class="grid sm:grid-cols-2 gap-2">
                    <div class="rounded-lg bg-emerald-50 border border-emerald-200 px-3 py-2">
                        <p class="text-[11px] text-emerald-700">إجمالي الرواتب</p>
                        <p class="font-bold text-emerald-800 num-ltr">{{ number_format((float) $depensesEmployeSalaires, 2, ',', ' ') }} MRU</p>
                    </div>
                    <div class="rounded-lg bg-indigo-50 border border-indigo-200 px-3 py-2">
                        <p class="text-[11px] text-indigo-700">إجمالي النقل</p>
                        <p class="font-bold text-indigo-800 num-ltr">{{ number_format((float) $depensesEmployeTransport, 2, ',', ' ') }} MRU</p>
                    </div>
                </div>

                <div class="table-wrap">
                    <table class="table-base">
                        <thead class="table-head">
                            <tr>
                                <th class="table-th">التاريخ</th>
                                <th class="table-th">النوع</th>
                                <th class="table-th">الوصف</th>
                                <th class="table-th">المبلغ</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($depensesEmploye as $depense)
                                <tr class="table-row">
                                    <td class="table-td">{{ $depense->date_depense?->format('d/m/Y') }}</td>
                                    <td class="table-td">{{ $depense->typeDepense?->libelle ?? '-' }}</td>
                                    <td class="table-td">{{ $depense->designation }}</td>
                                    <td class="table-td"><span class="num-ltr">{{ number_format((float) $depense->montant, 2, ',', ' ') }} MRU</span></td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="table-td text-center text-slate-500">لا توجد مصاريف مرتبطة بهذا الموظف.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endif
</div>
