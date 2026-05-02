<div class="page-container">
    <div class="page-header">
        <div>
            <h1 class="page-title">المصروفات والقروض</h1>
            <p class="page-subtitle">متابعة المصروفات اليومية والقروض المستلمة وتسديداتها.</p>
        </div>
        <div class="flex gap-2">
            @if($onglet === 'depenses')
                <a href="{{ route('exports.depenses.pdf') }}" class="btn-secondary">تصدير PDF</a>
                <button type="button" wire:click="nouvelleDepense" class="btn-primary">مصروف جديد</button>
            @else
                <button type="button" wire:click="nouveauPret" class="btn-primary">قرض جديد</button>
            @endif
        </div>
    </div>

    {{-- Onglets --}}
    <div class="flex gap-1 rounded-xl bg-slate-100 p-1 w-fit mb-4">
        <button type="button" wire:click="$set('onglet', 'depenses')"
            class="px-4 py-1.5 rounded-lg text-sm font-medium transition {{ $onglet === 'depenses' ? 'bg-white shadow text-slate-800' : 'text-slate-500 hover:text-slate-700' }}">
            <i class="fi fi-rr-money-bill-wave ml-1"></i> المصروفات
        </button>
        <button type="button" wire:click="$set('onglet', 'prets')"
            class="px-4 py-1.5 rounded-lg text-sm font-medium transition {{ $onglet === 'prets' ? 'bg-white shadow text-slate-800' : 'text-slate-500 hover:text-slate-700' }}">
            <i class="fi fi-rr-bank ml-1"></i> القروض
        </button>
    </div>

    {{-- ══════════════════════════════════════════════════════════
         ONGLET DÉPENSES
    ══════════════════════════════════════════════════════════ --}}
    @if($onglet === 'depenses')

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

        <div class="mb-3 text-sm text-gray-700">
            إجمالي الفترة: <strong class="num-ltr">{{ number_format((float) $totalPeriode, 2, ',', ' ') }} MRU</strong>
        </div>

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
                        @foreach($typesSaisie as $type)
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

                @php
                    $typeSelectionne = collect($types)->firstWhere('id', (int) $fkIdTypeDepense);
                    $estTransportSelectionne = $typeSelectionne?->libelle === 'النقل';
                @endphp
                @if($estTransportSelectionne)
                    <div>
                        <label class="block text-sm font-medium mb-1">الموظف</label>
                        <select wire:model.live="fkIdEmploye" class="w-full rounded border-gray-300">
                            <option value="">اختر الموظف...</option>
                            @foreach($employes as $employe)
                                <option value="{{ $employe->id }}">{{ $employe->full_name }}</option>
                            @endforeach
                        </select>
                        @error('fkIdEmploye') <div class="text-xs text-red-600 mt-1">{{ $message }}</div> @enderror
                    </div>
                @else
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
                @endif

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
                        <th class="table-th">الموظف / المورد</th>
                        <th class="table-th">التصنيف</th>
                        <th class="table-th"><button wire:click="sortBy('montant')" class="font-medium">المبلغ</button></th>
                        <th class="table-th"></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($depenses as $depense)
                        @php
                            $estDepenseEmploye = ($depense->typeDepense?->libelle === 'الرواتب')
                                || str_starts_with((string) $depense->reference, 'AVANCE-')
                                || str_starts_with((string) $depense->reference, 'PAIE-');
                            $estRemboursement = str_starts_with((string) $depense->reference, 'PRET-');
                        @endphp
                        <tr class="table-row">
                            <td class="table-td whitespace-nowrap">{{ $depense->date_depense?->format('d/m/Y') }}</td>
                            <td class="table-td">{{ $depense->typeDepense?->libelle }}</td>
                            <td class="table-td">{{ $depense->designation }}</td>
                            <td class="table-td text-slate-500 text-xs">
                                {{ $depense->employe?->full_name ?? $depense->fournisseur?->nom ?? '-' }}
                            </td>
                            <td class="table-td">
                                @if($estRemboursement)
                                    <span class="status-badge status-info">تسديد قرض</span>
                                @elseif($estDepenseEmploye)
                                    <span class="status-badge status-warning">موظفين</span>
                                @else
                                    <span class="status-badge status-neutral">عادي</span>
                                @endif
                            </td>
                            <td class="table-td whitespace-nowrap">
                                <span class="num-ltr">{{ number_format((float) $depense->montant, 2, ',', ' ') }} MRU</span>
                            </td>
                            <td class="table-td text-right">
                                @if($estDepenseEmploye || $estRemboursement)
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
                        <tr><td colspan="7" class="table-td text-center text-gray-500">لا توجد مصروفات.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-3">{{ $depenses->links() }}</div>

    @endif

    {{-- ══════════════════════════════════════════════════════════
         ONGLET PRÊTS
    ══════════════════════════════════════════════════════════ --}}
    @if($onglet === 'prets')

        {{-- Bilan global --}}
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-4">
            <div class="card card-body space-y-1">
                <p class="text-xs text-slate-500">إجمالي القروض المستلمة</p>
                <p class="text-xl font-bold text-indigo-700 num-ltr">{{ number_format($pretsTotaux['total_emprunte'], 2, ',', ' ') }} MRU</p>
            </div>
            <div class="card card-body space-y-1">
                <p class="text-xs text-slate-500">إجمالي المسدَّد</p>
                <p class="text-xl font-bold text-emerald-600 num-ltr">{{ number_format($pretsTotaux['total_rembourse'], 2, ',', ' ') }} MRU</p>
            </div>
            <div class="card card-body space-y-1 {{ $pretsTotaux['solde_restant'] > 0 ? 'border-amber-200 bg-amber-50' : '' }}">
                <p class="text-xs text-slate-500">الرصيد المتبقي</p>
                <p class="text-xl font-bold {{ $pretsTotaux['solde_restant'] > 0 ? 'text-amber-700' : 'text-slate-500' }} num-ltr">
                    {{ number_format($pretsTotaux['solde_restant'], 2, ',', ' ') }} MRU
                </p>
            </div>
        </div>

        {{-- Formulaire nouveau / modifier prêt --}}
        @if($afficherFormPret)
            <form wire:submit.prevent="sauvegarderPret" class="card card-body mb-4 grid md:grid-cols-2 gap-3">
                <h3 class="md:col-span-2 font-semibold text-slate-800">{{ $pretEditId ? 'تعديل القرض' : 'تسجيل قرض جديد' }}</h3>

                @if($errors->any())
                    <div class="md:col-span-2 rounded border border-red-300 bg-red-50 px-3 py-2 text-sm text-red-700">
                        يرجى تصحيح الحقول التي تحتوي على أخطاء.
                    </div>
                @endif

                <div>
                    <label class="block text-sm font-medium mb-1">تاريخ الاستلام *</label>
                    <input wire:model.live="pretDatePret" type="date" class="w-full rounded border-gray-300">
                    @error('pretDatePret') <div class="text-xs text-red-600 mt-1">{{ $message }}</div> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium mb-1">اسم المُقرض *</label>
                    <input wire:model.live="pretPreteur" type="text" placeholder="اسم الشخص أو الجهة" class="w-full rounded border-gray-300">
                    @error('pretPreteur') <div class="text-xs text-red-600 mt-1">{{ $message }}</div> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium mb-1">مبلغ القرض *</label>
                    <input wire:model.live="pretMontant" type="number" step="0.01" min="0.01" placeholder="0.00" class="w-full rounded border-gray-300">
                    @error('pretMontant') <div class="text-xs text-red-600 mt-1">{{ $message }}</div> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium mb-1">طريقة الاستلام *</label>
                    <select wire:model.live="pretMode" class="w-full rounded border-gray-300">
                        @foreach($modes as $mode)
                            <option value="{{ $mode->code }}">{{ $mode->libelle }}</option>
                        @endforeach
                    </select>
                    @error('pretMode') <div class="text-xs text-red-600 mt-1">{{ $message }}</div> @enderror
                </div>

                <div class="md:col-span-2">
                    <label class="block text-sm font-medium mb-1">ملاحظات</label>
                    <textarea wire:model.live="pretNotes" rows="2" class="w-full rounded border-gray-300" placeholder="ملاحظات اختيارية"></textarea>
                </div>

                <div class="md:col-span-2 flex gap-2">
                    <button type="submit" class="btn-primary" wire:loading.attr="disabled">حفظ</button>
                    <button type="button" wire:click="fermerFormPret" class="btn-secondary">إلغاء</button>
                </div>
            </form>
        @endif

        {{-- Liste des prêts --}}
        <div class="space-y-3">
            @forelse($prets as $pret)
                <div class="card card-body" wire:key="pret-{{ $pret->id }}">
                    <div class="flex flex-wrap items-start justify-between gap-3 mb-3">
                        <div>
                            <div class="flex items-center gap-2 flex-wrap">
                                <span class="font-semibold text-slate-800">{{ $pret->preteur }}</span>
                                @if($pret->statut === 'solde')
                                    <span class="inline-flex items-center rounded-full bg-emerald-50 px-2 py-0.5 text-xs text-emerald-700">مسدَّد بالكامل ✓</span>
                                @else
                                    <span class="inline-flex items-center rounded-full bg-amber-50 px-2 py-0.5 text-xs text-amber-700">جاري التسديد</span>
                                @endif
                            </div>
                            <p class="text-xs text-slate-500 mt-0.5">
                                <span class="num-ltr">{{ $pret->date_pret->format('d/m/Y') }}</span>
                                &nbsp;·&nbsp; {{ $modes->firstWhere('code', $pret->mode_paiement)?->libelle ?? $pret->mode_paiement }}
                                @if($pret->notes) &nbsp;·&nbsp; {{ $pret->notes }} @endif
                            </p>
                        </div>
                        <div class="flex items-center gap-2">
                            @if($pret->statut !== 'solde')
                                <button wire:click="ouvrirRemboursement({{ $pret->id }})" class="btn-primary !text-xs !px-3 !py-1.5">
                                    <i class="fi fi-rr-plus mr-1"></i> تسديد
                                </button>
                            @endif
                            <button wire:click="editerPret({{ $pret->id }})" class="btn-secondary !text-xs !px-3 !py-1.5">
                                <i class="fi fi-rr-edit mr-1"></i> تعديل
                            </button>
                        </div>
                    </div>

                    {{-- Montants --}}
                    <div class="grid grid-cols-3 gap-3 mb-3">
                        <div class="rounded-lg bg-indigo-50 px-3 py-2">
                            <p class="text-[11px] text-indigo-600">مبلغ القرض</p>
                            <p class="font-bold text-indigo-800 num-ltr text-sm">{{ number_format((float) $pret->montant, 2, ',', ' ') }} MRU</p>
                        </div>
                        <div class="rounded-lg bg-emerald-50 px-3 py-2">
                            <p class="text-[11px] text-emerald-600">المسدَّد</p>
                            <p class="font-bold text-emerald-700 num-ltr text-sm">{{ number_format((float) $pret->montant_rembourse, 2, ',', ' ') }} MRU</p>
                        </div>
                        <div class="rounded-lg {{ $pret->solde_restant > 0 ? 'bg-amber-50' : 'bg-slate-50' }} px-3 py-2">
                            <p class="text-[11px] {{ $pret->solde_restant > 0 ? 'text-amber-600' : 'text-slate-500' }}">المتبقي</p>
                            <p class="font-bold {{ $pret->solde_restant > 0 ? 'text-amber-700' : 'text-slate-500' }} num-ltr text-sm">
                                {{ number_format($pret->solde_restant, 2, ',', ' ') }} MRU
                            </p>
                        </div>
                    </div>

                    {{-- Barre de progression --}}
                    <div class="space-y-1">
                        <div class="flex justify-between text-[11px] text-slate-500">
                            <span>نسبة التسديد</span>
                            <span class="num-ltr">{{ $pret->pourcentage_rembourse }}%</span>
                        </div>
                        <div class="h-2 w-full rounded-full bg-slate-100 overflow-hidden">
                            <div class="h-full rounded-full {{ $pret->statut === 'solde' ? 'bg-emerald-500' : 'bg-indigo-500' }} transition-all"
                                 style="width: {{ $pret->pourcentage_rembourse }}%"></div>
                        </div>
                    </div>

                    {{-- Historique remboursements --}}
                    @php
                        $remboursements = \App\Models\Depense::query()
                            ->forCurrentSuccursale()
                            ->where('reference', 'PRET-' . $pret->id)
                            ->validee()
                            ->orderByDesc('date_depense')
                            ->get();
                    @endphp
                    @if($remboursements->isNotEmpty())
                        <div class="mt-3 border-t border-slate-100 pt-3">
                            <p class="text-xs font-medium text-slate-600 mb-2">سجل التسديدات</p>
                            <div class="space-y-1">
                                @foreach($remboursements as $remb)
                                    <div class="flex items-center justify-between text-xs text-slate-600 py-1 border-b border-slate-50 last:border-0">
                                        <span class="num-ltr text-slate-400">{{ $remb->date_depense->format('d/m/Y') }}</span>
                                        <span>{{ $modes->firstWhere('code', $remb->mode_paiement)?->libelle ?? $remb->mode_paiement }}</span>
                                        <span class="font-medium text-emerald-700 num-ltr">{{ number_format((float) $remb->montant, 2, ',', ' ') }} MRU</span>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>
            @empty
                <div class="card card-body text-center text-slate-400 py-10">
                    <i class="fi fi-rr-bank text-3xl block mb-2"></i>
                    لا توجد قروض مسجلة.
                </div>
            @endforelse
        </div>
        <div class="mt-3">{{ $prets->links() }}</div>

    @endif

    {{-- Modale confirmation annulation dépense --}}
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

    {{-- Modale remboursement --}}
    @if($afficherFormRemboursement && $pretARembourserId)
        @php $pretCourant = $prets->firstWhere('id', $pretARembourserId); @endphp
        <div class="modal-overlay flex items-center justify-center p-4">
            <div class="modal-panel max-w-md w-full p-5 space-y-4">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-base font-semibold text-slate-900">تسجيل تسديد</h3>
                        @if($pretCourant)
                            <p class="text-xs text-slate-500 mt-0.5">
                                {{ $pretCourant->preteur }}
                                &nbsp;·&nbsp; المتبقي:
                                <span class="font-medium text-amber-700 num-ltr">{{ number_format($pretCourant->solde_restant, 2, ',', ' ') }} MRU</span>
                            </p>
                        @endif
                    </div>
                    <button wire:click="fermerRemboursement" class="btn-secondary">إغلاق</button>
                </div>

                <form wire:submit.prevent="sauvegarderRemboursement" class="space-y-3">
                    @if($errors->any())
                        <div class="rounded border border-red-300 bg-red-50 px-3 py-2 text-sm text-red-700">
                            يرجى تصحيح الحقول التي تحتوي على أخطاء.
                        </div>
                    @endif

                    <div>
                        <label class="block text-sm font-medium mb-1">تاريخ التسديد *</label>
                        <input wire:model.live="rembDate" type="date" class="w-full rounded border-gray-300">
                        @error('rembDate') <div class="text-xs text-red-600 mt-1">{{ $message }}</div> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium mb-1">المبلغ المسدَّد *</label>
                        <input wire:model.live="rembMontant" type="number" step="0.01" min="0.01" placeholder="0.00" class="w-full rounded border-gray-300">
                        @error('rembMontant') <div class="text-xs text-red-600 mt-1">{{ $message }}</div> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium mb-1">طريقة الدفع *</label>
                        <select wire:model.live="rembMode" class="w-full rounded border-gray-300">
                            @foreach($modes as $mode)
                                <option value="{{ $mode->code }}">{{ $mode->libelle }}</option>
                            @endforeach
                        </select>
                        @error('rembMode') <div class="text-xs text-red-600 mt-1">{{ $message }}</div> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium mb-1">ملاحظات</label>
                        <textarea wire:model.live="rembNotes" rows="2" class="w-full rounded border-gray-300" placeholder="اختياري"></textarea>
                    </div>

                    <div class="flex gap-2 justify-end pt-1">
                        <button type="button" wire:click="fermerRemboursement" class="btn-secondary">إلغاء</button>
                        <button type="submit" class="btn-primary" wire:loading.attr="disabled">تأكيد التسديد</button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>
