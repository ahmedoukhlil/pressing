<div class="page-container space-y-6">
    <div class="page-header">
        <div>
            <h1 class="page-title">الطلبات</h1>
            <p class="page-subtitle">بحث ومتابعة الحالة وتحصيل الباقي.</p>
        </div>
        <a href="{{ route('exports.commandes.pdf') }}" class="btn-secondary">تصدير PDF</a>
    </div>

    @if($messageSucces)
        <div class="alert alert-success">{{ $messageSucces }}</div>
    @endif
    @if($messageErreur)
        <div class="alert alert-error">{{ $messageErreur }}</div>
    @endif

    <div class="card card-body">
        <div class="grid md:grid-cols-3 gap-2">
            <input wire:model.live.debounce.400ms="recherche" wire:keydown.enter.prevent="rechercherCommande" type="text" placeholder="رقم الطلب، الهاتف، أو اسم الزبون" class="form-field md:col-span-2">
            <select wire:model.live="filtreStatut" class="form-field">
                <option value="">كل الحالات</option>
                <option value="en_cours">قيد المعالجة</option>
                <option value="pret">جاهز</option>
                <option value="livre">مسلّم</option>
            </select>
        </div>
        <div class="mt-2 flex flex-wrap items-center gap-2">
            <button type="button" wire:click="$toggle('afficherFiltresAvances')" class="btn-secondary text-xs">
                <i class="fi fi-rr-settings-sliders mr-1"></i>
                {{ $afficherFiltresAvances ? 'إخفاء الفلاتر المتقدمة' : 'إظهار الفلاتر المتقدمة' }}
            </button>
            @if($recherche !== '' || $filtreStatut !== '' || $dateDebut !== '' || $dateFin !== '')
                <button wire:click="reinitialiserFiltres" class="btn-secondary text-xs">
                    <i class="fi fi-rr-cross-small mr-1"></i> مسح الفلاتر
                </button>
            @endif
        </div>
        @if($afficherFiltresAvances)
            <div class="mt-2 grid md:grid-cols-2 gap-2 items-end">
                <div>
                    <label class="text-xs text-slate-500 mb-0.5 block">من تاريخ</label>
                    <input wire:model.live="dateDebut" type="date" class="form-field">
                </div>
                <div>
                    <label class="text-xs text-slate-500 mb-0.5 block">إلى تاريخ</label>
                    <input wire:model.live="dateFin" type="date" class="form-field">
                </div>
            </div>
        @endif
        <div class="mt-3 flex flex-wrap items-center gap-2 rounded-lg border border-slate-200 bg-slate-50 p-2">
            <span class="text-xs text-slate-500">إجراءات جماعية (فقط للطلبات قيد المعالجة)</span>
            <select wire:model="statutGroupe" class="form-field max-w-xs">
                <option value="">اختر الإجراء</option>
                <option value="pret">تحويل المحدد إلى جاهز</option>
            </select>
            <button wire:click="appliquerChangementStatutGroupe" class="btn-secondary" @disabled(empty($selectionCommandes))>
                تطبيق جماعي
            </button>
            <span class="text-xs text-gray-500">المحدد: <span class="num-ltr">{{ count($selectionCommandes) }}</span></span>
        </div>
    </div>

    <div class="grid lg:grid-cols-2 gap-6">
        <div class="table-wrap">
            <table class="table-base">
                <thead class="table-head">
                    <tr>
                        <th class="table-th text-center">
                            <input type="checkbox" wire:model.live="selectionPage" class="rounded border-slate-300">
                        </th>
                        <th class="table-th">الرقم</th>
                        <th class="table-th">الزبون</th>
                        <th class="table-th">الحالة</th>
                        <th class="table-th text-right">الإجراءات</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($resultats as $item)
                        <tr class="table-row {{ $commandeSelectionneeId === $item->id ? 'bg-blue-50/50' : '' }}">
                            <td class="table-td text-center">
                                <input type="checkbox" value="{{ $item->id }}" wire:model.live="selectionCommandes" class="rounded border-slate-300">
                            </td>
                            <td class="table-td">{{ $item->numero_commande }}</td>
                            <td class="table-td">{{ $item->client?->full_name }}</td>
                            <td class="table-td">
                                <span class="status-badge {{ $item->statut === 'livre' ? 'status-success' : ($item->statut === 'pret' ? 'status-warning' : 'status-neutral') }}">
                                    {{ $item->statut_label }}
                                </span>
                            </td>
                            <td class="table-td text-right">
                                <div class="inline-flex items-center gap-2">
                                    <button wire:click="selectionnerCommande({{ $item->id }})" class="btn-ghost text-blue-700">فتح</button>
                                    @role('gerant')
                                        <button wire:click="demanderSuppressionCommande({{ $item->id }})" class="btn-ghost text-red-600">حذف</button>
                                    @endrole
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="table-td text-center text-slate-500">لا توجد نتائج.</td></tr>
                    @endforelse
                </tbody>
            </table>
            <div class="p-3 border-t border-slate-200">
                {{ $resultats->links() }}
            </div>
        </div>

        <div class="card card-body">
            @if($commande)
                <div class="space-y-2 text-sm">
                    <div><strong>{{ $commande->numero_commande }}</strong></div>
                    <div>الزبون: {{ $commande->client?->full_name }} - {{ $commande->client?->telephone }}</div>
                    <div>الحالة: {{ $commande->statut_label }}</div>
                    <div>إجمالي قبل الخصم: <span class="num-ltr">{{ number_format((float) $commande->montant_total + (float) $commande->total_remise, 2, ',', ' ') }} MRU</span></div>
                    <div>إجمالي الخصومات: <span class="num-ltr">{{ number_format((float) $commande->total_remise, 2, ',', ' ') }} MRU</span></div>
                    <div>الإجمالي بعد الخصم: <span class="num-ltr">{{ number_format((float) $commande->montant_total, 2, ',', ' ') }} MRU</span></div>
                    <div>المدفوع: <span class="num-ltr">{{ number_format((float) $commande->montant_paye, 2, ',', ' ') }} MRU</span></div>
                    <div>المتبقي: <span class="num-ltr">{{ number_format((float) $commande->reste_a_payer, 2, ',', ' ') }} MRU</span></div>
                </div>

                <div class="mt-3 table-wrap">
                    <table class="table-base">
                        <thead class="table-head">
                            <tr>
                                <th class="table-th">الخدمة</th>
                                <th class="table-th">الكمية</th>
                                <th class="table-th">السعر</th>
                                <th class="table-th">الإجمالي</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($commande->details as $detail)
                                <tr class="table-row">
                                    <td class="table-td">{{ $detail->service?->libelle_ar ?: '-' }}</td>
                                    <td class="table-td"><span class="num-ltr">{{ (int) $detail->quantite }}</span></td>
                                    <td class="table-td"><span class="num-ltr">{{ number_format((float) $detail->prix_unitaire, 2, ',', ' ') }} MRU</span></td>
                                    <td class="table-td"><span class="num-ltr">{{ number_format((float) $detail->sous_total, 2, ',', ' ') }} MRU</span></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="mt-4 flex flex-wrap gap-2">
                    @if((float)$commande->reste_a_payer > 0)
                        <button wire:click="ouvrirPaiement" class="btn-primary" wire:loading.attr="disabled">تحصيل الباقي</button>
                    @endif
                    @if($commande->statut === 'en_cours')
                        <button wire:click="confirmerChangementStatut({{ $commande->id }}, 'pret')" class="btn-secondary">تحديد كجاهز</button>
                    @endif
                    @if($commande->statut === 'pret')
                        <button wire:click="confirmerChangementStatut({{ $commande->id }}, 'livre')" class="btn-primary">تحديد كمسلّم</button>
                    @endif
                    <a href="{{ route('commandes.ticket', $commande) }}" target="_blank" class="btn-secondary">طباعة الوصل</a>
                    @role('gerant')
                        <button wire:click="demanderSuppressionCommande({{ $commande->id }})" class="btn-danger">حذف الطلب</button>
                    @endrole
                </div>
            @else
                <div class="text-sm text-gray-500">اختر طلبًا لعرض التفاصيل.</div>
            @endif
        </div>
    </div>

    @if($afficherPaiement && $commande)
        <div class="modal-overlay flex items-center justify-center p-4">
            <div class="modal-panel max-w-md p-4 space-y-3">
                <div class="text-lg font-medium">تحصيل المبلغ المتبقي</div>
                <div>
                    <label class="text-sm">المبلغ المطلوب دفعه</label>
                    <input type="number" step="0.01" min="0" wire:model="montantAPayer" class="form-field">
                    @error('montantAPayer') <div class="form-error">{{ $message }}</div> @enderror
                </div>
                <div>
                    <label class="text-sm">نسبة الخصم عند التسوية (%)</label>
                    <input type="number" step="0.01" min="0" max="100" wire:model="remisePourcentage" class="form-field">
                    <div class="mt-1 text-xs text-gray-500">
                        قيمة الخصم: <span class="num-ltr">{{ number_format((float) $this->remise_montant, 2, ',', ' ') }} MRU</span><br>
                        المتبقي بعد الخصم: <span class="num-ltr">{{ number_format((float) $this->reste_apres_remise, 2, ',', ' ') }} MRU</span>
                    </div>
                    @error('remisePourcentage') <div class="form-error">{{ $message }}</div> @enderror
                </div>
                <div>
                    <label class="text-sm">طريقة الدفع</label>
                    <select wire:model="modeReglement" class="form-field">
                        @forelse($modesPaiement as $mode)
                            <option value="{{ $mode->code }}">
                                {{ $mode->icone ? $mode->icone . ' ' : '' }}{{ $mode->libelle }}
                            </option>
                        @empty
                            <option value="especes">نقدًا</option>
                            <option value="carte">بطاقة</option>
                            <option value="virement">تحويل</option>
                        @endforelse
                    </select>
                    @error('modeReglement') <div class="form-error">{{ $message }}</div> @enderror
                </div>
                <div class="flex justify-end gap-2">
                    <button wire:click="$set('afficherPaiement', false)" class="btn-secondary">إلغاء</button>
                    <button wire:click="encaisserReste" class="btn-primary" wire:loading.attr="disabled">تأكيد</button>
                </div>
            </div>
        </div>
    @endif

    @if($afficherConfirmationStatut)
        <div class="modal-overlay flex items-center justify-center p-4">
            <div class="modal-panel max-w-md p-4 space-y-3">
                <div class="text-lg font-medium">تأكيد تغيير الحالة</div>
                <p class="text-sm text-slate-600">
                    @if($statutAConfirmer === 'pret')
                        هل تريد تحديد الطلب كـ <strong>جاهز</strong>؟
                    @elseif($statutAConfirmer === 'livre')
                        هل تريد تحديد الطلب كـ <strong>مسلّم</strong>؟
                    @else
                        هل تريد تغيير حالة الطلب؟
                    @endif
                </p>
                <div class="flex justify-end gap-2">
                    <button wire:click="annulerConfirmationStatut" class="btn-secondary">إلغاء</button>
                    <button wire:click="validerChangementStatut" class="btn-primary">تأكيد</button>
                </div>
            </div>
        </div>
    @endif

    @if($afficherConfirmationSuppression)
        <div class="modal-overlay flex items-center justify-center p-4">
            <div class="modal-panel max-w-md p-4 space-y-3">
                <div class="text-lg font-medium text-red-700">تأكيد حذف الطلب</div>
                <p class="text-sm text-slate-600">
                    هل تريد حذف الطلب <strong>{{ $numeroCommandeASupprimer }}</strong>؟
                </p>
                <p class="text-xs text-slate-500">
                    سيتم حذف تفاصيل الطلب نهائيًا.
                </p>
                <div class="flex justify-end gap-2">
                    <button wire:click="annulerSuppressionCommande" class="btn-secondary">إلغاء</button>
                    <button wire:click="confirmerSuppressionCommande" class="btn-danger">حذف</button>
                </div>
            </div>
        </div>
    @endif
</div>
