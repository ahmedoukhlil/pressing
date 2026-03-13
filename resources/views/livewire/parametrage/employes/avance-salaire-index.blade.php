<div class="page-container">
    <div class="page-header">
        <div>
            <h1 class="page-title">
                {{ $vuePaiementSalaire ? 'دفع الراتب' : 'سلف الرواتب' }} - {{ $employe->full_name }}
            </h1>
            <p class="page-subtitle">
                {{ $vuePaiementSalaire
                    ? 'كشف راتب كامل مع خصم تلقائي للسلف.'
                    : 'متابعة السلف مع الخصم والإلغاء المحاسبي.' }}
            </p>
        </div>
        <div class="flex gap-2">
            @if($vuePaiementSalaire)
                <a href="{{ route('parametrage.employes.avances', $employe->id) }}" class="btn-secondary">عرض السلف</a>
                <button wire:click="ouvrirPaiementSalaire" class="btn-primary">دفع الراتب</button>
            @else
                <a href="{{ route('parametrage.employes.paiement', $employe->id) }}" class="btn-secondary">دفع الراتب</a>
                <button wire:click="ouvrirFormAvance" class="btn-primary">سلفة جديدة</button>
            @endif
        </div>
    </div>

    <div class="grid md:grid-cols-3 gap-3 mb-4">
        <div class="card card-body text-sm">الراتب الإجمالي: <strong class="num-ltr">{{ number_format((float) $employe->salaire_brut, 2, ',', ' ') }} MRU</strong></div>
        <div class="card card-body text-sm">السلف الحالية: <strong class="num-ltr">{{ number_format((float) $employe->total_avances_en_cours, 2, ',', ' ') }} MRU</strong></div>
        <div class="card card-body text-sm">صافي الراتب المتوقع: <strong class="num-ltr">{{ number_format((float) $employe->salaire_net, 2, ',', ' ') }} MRU</strong></div>
    </div>

    @if($afficherPaiementSalaire || $vuePaiementSalaire)
        <form wire:submit.prevent="payerSalaire" class="card card-body mb-4">
            <div class="mb-3">
                <h2 class="card-title">دفع الراتب - كشف كامل</h2>
                <p class="text-sm text-slate-600">يتم احتساب الصافي المدفوع تلقائيًا (الراتب الإجمالي - السلف الحالية).</p>
            </div>

            <div class="grid md:grid-cols-3 gap-3 mb-3">
                <div class="rounded-lg border border-slate-200 bg-slate-50 p-3 text-sm">
                    الراتب الإجمالي
                    <div class="num-ltr mt-1 text-base font-semibold text-slate-900">{{ number_format((float) $employe->salaire_brut, 2, ',', ' ') }} MRU</div>
                </div>
                <div class="rounded-lg border border-amber-200 bg-amber-50 p-3 text-sm">
                    إجمالي السلف المراد خصمها
                    <div class="num-ltr mt-1 text-base font-semibold text-amber-800">{{ number_format((float) $employe->total_avances_en_cours, 2, ',', ' ') }} MRU</div>
                </div>
                <div class="rounded-lg border border-emerald-200 bg-emerald-50 p-3 text-sm">
                    الصافي المستحق للدفع
                    <div class="num-ltr mt-1 text-base font-semibold text-emerald-800">{{ number_format((float) $employe->salaire_net, 2, ',', ' ') }} MRU</div>
                </div>
            </div>

            <div class="grid md:grid-cols-2 gap-3">
                <div>
                    <label class="form-label">تاريخ الدفع *</label>
                    <input wire:model.live="datePaiementSalaire" type="date" class="form-field">
                    @error('datePaiementSalaire') <div class="form-error">{{ $message }}</div> @enderror
                </div>
                <div>
                    <label class="form-label">طريقة الدفع *</label>
                    <select wire:model.live="modePaiementSalaire" class="form-field">
                        @foreach($modesPaiement as $mode)
                            <option value="{{ $mode->code }}">{{ $mode->libelle }}</option>
                        @endforeach
                    </select>
                    @error('modePaiementSalaire') <div class="form-error">{{ $message }}</div> @enderror
                </div>
                <div class="md:col-span-2">
                    <label class="form-label">ملاحظات الراتب</label>
                    <textarea wire:model.live="notesPaiementSalaire" rows="2" class="form-field" placeholder="تعليق اختياري"></textarea>
                    @error('notesPaiementSalaire') <div class="form-error">{{ $message }}</div> @enderror
                </div>
            </div>

            <div class="mt-3 flex gap-2">
                <button type="submit" class="btn-primary" wire:loading.attr="disabled">تأكيد الدفع</button>
                @if($vuePaiementSalaire)
                    <a href="{{ route('parametrage.employes.avances', $employe->id) }}" class="btn-secondary">رجوع إلى السلف</a>
                @else
                    <button type="button" wire:click="$set('afficherPaiementSalaire', false)" class="btn-secondary">إغلاق</button>
                @endif
            </div>
        </form>
    @endif

    @if($messageSucces)
        <div class="alert alert-success mb-3">{{ $messageSucces }}</div>
    @endif
    @if($messageErreur)
        <div class="alert alert-error mb-3">{{ $messageErreur }}</div>
    @endif

    @if(!$vuePaiementSalaire && $afficherForm)
        <form wire:submit.prevent="enregistrerAvance" class="card card-body mb-4 grid md:grid-cols-2 gap-3">
            <div>
                <label class="form-label">تاريخ السلفة *</label>
                <input wire:model.live="dateAvance" type="date" class="form-field">
                @error('dateAvance') <div class="form-error">{{ $message }}</div> @enderror
            </div>
            <div>
                <label class="form-label">المبلغ *</label>
                <input wire:model.live="montant" type="number" min="1" step="0.01" placeholder="المبلغ" class="form-field">
                @error('montant') <div class="form-error">{{ $message }}</div> @enderror
            </div>
            <div class="md:col-span-2">
                <label class="form-label">السبب</label>
                <input wire:model.live="motif" type="text" placeholder="السبب" class="form-field">
                @error('motif') <div class="form-error">{{ $message }}</div> @enderror
            </div>
            <div class="md:col-span-2">
                <label class="form-label">ملاحظات</label>
                <textarea wire:model.live="notes" rows="2" placeholder="ملاحظات" class="form-field"></textarea>
            </div>
            <div class="md:col-span-2 flex gap-2">
                <button type="submit" class="btn-primary" wire:loading.attr="disabled">حفظ</button>
                <button type="button" wire:click="$set('afficherForm', false)" class="btn-secondary">إلغاء</button>
            </div>
        </form>
    @endif

    @if(!$vuePaiementSalaire)
        <div class="table-wrap">
            <table class="table-base">
                <thead class="table-head">
                    <tr>
                        <th class="table-th">التاريخ</th>
                        <th class="table-th">المبلغ</th>
                        <th class="table-th">الحالة</th>
                        <th class="table-th">السبب</th>
                        <th class="table-th text-right">الإجراءات</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($avances as $avance)
                        <tr class="table-row">
                            <td class="table-td">{{ $avance->date_avance?->format('d/m/Y') }}</td>
                            <td class="table-td"><span class="num-ltr">{{ number_format((float) $avance->montant, 2, ',', ' ') }} MRU</span></td>
                            <td class="table-td">
                                <span class="status-badge {{ $avance->statut === 'en_cours' ? 'status-warning' : ($avance->statut === 'deduite' ? 'status-success' : 'status-neutral') }}">{{ ucfirst($avance->statut) }}</span>
                            </td>
                            <td class="table-td">{{ $avance->motif ?: '-' }}</td>
                            <td class="table-td text-right">
                                @if($avance->statut === 'en_cours')
                                    <button wire:click="deduireAvance({{ $avance->id }})" onclick="return confirm('تأكيد خصم هذه السلفة؟')" class="text-green-700 text-xs">خصم</button>
                                    <button wire:click="annulerAvance({{ $avance->id }})" onclick="return confirm('تأكيد إلغاء هذه السلفة؟')" class="text-red-700 text-xs ml-2">إلغاء</button>
                                @else
                                    <span class="text-xs text-slate-400">-</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="table-td text-center text-gray-500">لا توجد سلف.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    @endif
</div>
