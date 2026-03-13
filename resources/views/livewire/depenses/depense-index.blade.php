<div class="page-container">
    <div class="page-header">
        <div>
            <h1 class="page-title">المصروفات</h1>
            <p class="page-subtitle">متابعة المصروفات مع الفلاتر والترتيب وإلغاء العملية.</p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('exports.depenses.pdf') }}" class="btn-secondary">تصدير PDF</a>
            <button type="button" wire:click.prevent="nouvelleDepense" wire:loading.attr="disabled" class="btn-primary">مصروف جديد</button>
        </div>
    </div>

    <div class="card card-body mb-4">
        <div class="grid md:grid-cols-2 gap-2">
        <select wire:model.live="filtrePeriode" class="form-field">
            <option value="jour">اليوم</option>
            <option value="semaine">الأسبوع</option>
            <option value="mois">الشهر</option>
            <option value="tous">الكل</option>
        </select>
        <input wire:model.live.debounce.500ms="recherche" type="text" placeholder="ابحث في الوصف" class="form-field">
        </div>
        <div class="mt-2">
            <button type="button" wire:click="$toggle('afficherFiltresAvances')" class="btn-secondary text-xs">
                <i class="fi fi-rr-settings-sliders mr-1"></i>
                {{ $afficherFiltresAvances ? 'إخفاء الفلاتر المتقدمة' : 'إظهار الفلاتر المتقدمة' }}
            </button>
        </div>
        @if($afficherFiltresAvances)
            <div class="mt-2 grid md:grid-cols-2 gap-2">
                <select wire:model.live="filtreCategorie" class="form-field">
            <option value="toutes">كل المصروفات</option>
            <option value="ordinaires">مصروفات عادية</option>
            <option value="employes">مصروفات الموظفين</option>
        </select>
        <select wire:model.live="filtreType" class="form-field">
            <option value="">كل الأنواع</option>
            @foreach($types as $type)
                <option value="{{ $type->id }}">{{ $type->libelle }}</option>
            @endforeach
        </select>
            </div>
        @endif
    </div>

    <div class="mb-3 text-sm text-gray-700">إجمالي الفترة: <strong class="num-ltr">{{ number_format((float) $totalPeriode, 2, ',', ' ') }} MRU</strong></div>

    @if($afficherForm)
        <form wire:submit.prevent="sauvegarder" class="card card-body mb-4 grid md:grid-cols-2 gap-3">
            @if ($errors->any())
                <div class="md:col-span-2 rounded border border-red-300 bg-red-50 px-3 py-2 text-sm text-red-700">
                    يرجى تصحيح الحقول التي تحتوي على أخطاء.
                </div>
            @endif

            <div>
                <label class="block text-sm font-medium mb-1">التاريخ *</label>
                <input wire:model.live="dateDepense" type="date" class="w-full rounded border-gray-300">
                @error('dateDepense') <div class="text-xs text-red-600 mt-1">{{ $message }}</div> @enderror
            </div>

            <div>
                <label class="block text-sm font-medium mb-1">نوع المصروف *</label>
                <select wire:model.live="fkIdTypeDepense" class="w-full rounded border-gray-300">
                    <option value="">اختر...</option>
                    @foreach($types as $type)
                        <option value="{{ $type->id }}">{{ $type->libelle }}</option>
                    @endforeach
                </select>
                @error('fkIdTypeDepense') <div class="text-xs text-red-600 mt-1">{{ $message }}</div> @enderror
            </div>

            <div class="md:col-span-2">
                <label class="block text-sm font-medium mb-1">الوصف *</label>
                <input wire:model.live="designation" type="text" placeholder="الوصف" class="w-full rounded border-gray-300">
                @error('designation') <div class="text-xs text-red-600 mt-1">{{ $message }}</div> @enderror
            </div>

            <div>
                <label class="block text-sm font-medium mb-1">المبلغ *</label>
                <input wire:model.live="montant" type="number" step="0.01" min="0.01" placeholder="المبلغ" class="w-full rounded border-gray-300">
                @error('montant') <div class="text-xs text-red-600 mt-1">{{ $message }}</div> @enderror
            </div>

            <div>
                <label class="block text-sm font-medium mb-1">طريقة الدفع *</label>
                <select wire:model.live="modePaiement" class="w-full rounded border-gray-300">
                    @foreach($modes as $mode)
                        <option value="{{ $mode->code }}">{{ $mode->libelle }}</option>
                    @endforeach
                </select>
                @error('modePaiement') <div class="text-xs text-red-600 mt-1">{{ $message }}</div> @enderror
            </div>

            <div>
                <label class="block text-sm font-medium mb-1">المورد</label>
                <select wire:model.live="fkIdFournisseur" class="w-full rounded border-gray-300">
                    <option value="">بدون مورد</option>
                    @foreach($fournisseurs as $f)
                        <option value="{{ $f->id }}">{{ $f->nom }}</option>
                    @endforeach
                </select>
                @error('fkIdFournisseur') <div class="text-xs text-red-600 mt-1">{{ $message }}</div> @enderror
            </div>

            <div>
                <label class="block text-sm font-medium mb-1">المرجع</label>
                <input wire:model.live="reference" type="text" placeholder="المرجع" class="w-full rounded border-gray-300">
            </div>

            <div class="md:col-span-2">
                <label class="block text-sm font-medium mb-1">ملاحظات</label>
                <textarea wire:model.live="notes" rows="2" class="w-full rounded border-gray-300" placeholder="ملاحظات"></textarea>
            </div>

            <div class="md:col-span-2 flex gap-2">
                        <button type="submit" class="btn-primary" wire:loading.attr="disabled">حفظ</button>
                        <button type="button" wire:click="fermerForm" class="btn-secondary">إلغاء</button>
            </div>
        </form>
    @endif

    <div class="table-wrap">
        <table class="table-base">
            <thead class="table-head">
                <tr>
                    <th class="table-th"><button wire:click="sortBy('date_depense')" class="font-medium">التاريخ</button></th>
                    <th class="table-th"><button wire:click="sortBy('fk_id_type_depense')" class="font-medium">النوع</button></th>
                    <th class="table-th"><button wire:click="sortBy('designation')" class="font-medium">الوصف</button></th>
                    <th class="table-th">التصنيف</th>
                    <th class="table-th"><button wire:click="sortBy('montant')" class="font-medium">المبلغ</button></th>
                    <th class="table-th"></th>
                </tr>
            </thead>
            <tbody>
                @forelse($depenses as $depense)
                    @php
                        $estDepenseEmploye = ($depense->typeDepense?->libelle === 'Salaires')
                            || str_starts_with((string) $depense->reference, 'AVANCE-')
                            || str_starts_with((string) $depense->reference, 'PAIE-');
                    @endphp
                    <tr class="table-row">
                        <td class="table-td">{{ $depense->date_depense?->format('d/m/Y') }}</td>
                        <td class="table-td">{{ $depense->typeDepense?->libelle }}</td>
                        <td class="table-td">{{ $depense->designation }}</td>
                        <td class="table-td">
                            <span class="status-badge {{ $estDepenseEmploye ? 'status-warning' : 'status-neutral' }}">
                                {{ $estDepenseEmploye ? 'موظفين' : 'عادي' }}
                            </span>
                        </td>
                        <td class="table-td"><span class="num-ltr">{{ number_format((float) $depense->montant, 2, ',', ' ') }} MRU</span></td>
                        <td class="table-td text-right">
                            @if($estDepenseEmploye)
                                <span class="text-xs text-slate-400">تلقائي</span>
                            @else
                                <div class="inline-flex items-center gap-2">
                                    <button wire:click="editer({{ $depense->id }})" class="btn-ghost !px-2.5 !py-1.5 !text-xs text-blue-700">
                                        <i class="fi fi-rr-edit mr-1"></i> تعديل
                                    </button>
                                    <button wire:click="demanderAnnulation({{ $depense->id }})" class="btn-ghost !px-2.5 !py-1.5 !text-xs text-red-700 hover:!bg-red-50">
                                        <i class="fi fi-rr-ban mr-1"></i> إلغاء
                                    </button>
                                </div>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="table-td text-center text-gray-500">لا توجد مصروفات.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-3">{{ $depenses->links() }}</div>

    @if($depenseAAnnulerId)
        <div class="modal-overlay flex items-center justify-center p-4">
            <div class="modal-panel max-w-md p-4 space-y-3">
                <div class="text-lg font-medium">تأكيد إلغاء المصروف</div>
                <p class="text-sm text-slate-600">هل تريد بالتأكيد إلغاء هذا المصروف؟ لا يمكن التراجع عن العملية.</p>
                <div class="flex justify-end gap-2">
                    <button wire:click="annulerAnnulation" class="btn-secondary">رجوع</button>
                    <button wire:click="confirmerAnnulation" class="btn-danger">تأكيد الإلغاء</button>
                </div>
            </div>
        </div>
    @endif
</div>
