<div class="page-container space-y-3">
    <div class="page-header">
        <div>
            <h1 class="page-title">الطلبات</h1>
            <p class="page-subtitle">بحث ومتابعة الحالة وتحصيل الباقي.</p>
        </div>
        <div class="flex items-center gap-2">
            <button
                type="button"
                wire:click="ouvrirRappelsModal"
                class="btn-secondary relative"
            >
                <i class="fi fi-rr-bell-ring mr-1"></i> تذكيرات
                <span class="ms-1 inline-flex items-center justify-center rounded-full bg-amber-500 px-1.5 py-0.5 text-[10px] font-bold text-white num-ltr">
                    {{ $commandesARappeler->count() }}
                </span>
            </button>
            <a href="{{ route('exports.commandes.pdf') }}" class="btn-secondary">
                <i class="fi fi-rr-file-pdf mr-1"></i> تصدير PDF
            </a>
        </div>
    </div>

    {{-- ═══ Filtres ═══ --}}
    <div class="card card-body !py-2 !px-3">
        <div class="flex flex-wrap items-center gap-2">
            <div class="relative flex-1 min-w-[200px] max-w-md">
                <input
                    wire:model.live.debounce.400ms="recherche"
                    wire:keydown.enter.prevent="rechercherCommande"
                    type="text"
                    placeholder="رقم الطلب، الهاتف، أو اسم الزبون..."
                    class="form-field !h-8 pe-8 w-full"
                >
                <i class="fi fi-rr-search absolute top-1/2 -translate-y-1/2 end-2 text-slate-400 text-[10px] pointer-events-none"></i>
            </div>

            <div class="flex items-center gap-1 rounded-md border border-slate-200 p-0.5">
                <button wire:click="$set('filtreStatut', '')"
                    class="px-2 py-1 rounded text-[11px] font-medium transition {{ $filtreStatut === '' ? 'bg-slate-800 text-white' : 'text-slate-500 hover:text-slate-700' }}">
                    الكل
                </button>
                <button wire:click="$set('filtreStatut', 'en_cours')"
                    class="px-2 py-1 rounded text-[11px] font-medium transition {{ $filtreStatut === 'en_cours' ? 'bg-amber-500 text-white' : 'text-slate-500 hover:text-slate-700' }}">
                    قيد المعالجة
                </button>
                <button wire:click="$set('filtreStatut', 'pret')"
                    class="px-2 py-1 rounded text-[11px] font-medium transition {{ $filtreStatut === 'pret' ? 'bg-blue-500 text-white' : 'text-slate-500 hover:text-slate-700' }}">
                    جاهز
                </button>
                <button wire:click="$set('filtreStatut', 'livre')"
                    class="px-2 py-1 rounded text-[11px] font-medium transition {{ $filtreStatut === 'livre' ? 'bg-emerald-500 text-white' : 'text-slate-500 hover:text-slate-700' }}">
                    مسلّم
                </button>
            </div>

            <button type="button" wire:click="$toggle('afficherFiltresAvances')" class="text-[11px] text-slate-500 hover:text-slate-700">
                <i class="fi fi-rr-calendar mr-0.5"></i> التاريخ
            </button>

            @if($recherche !== '' || $filtreStatut !== '' || $dateDebut !== '' || $dateFin !== '')
                <button wire:click="reinitialiserFiltres" class="text-[11px] text-red-500 hover:text-red-700">
                    <i class="fi fi-rr-cross-small"></i> مسح
                </button>
            @endif
        </div>

        @if($afficherFiltresAvances)
            <div class="mt-2 flex flex-wrap items-center gap-2 pt-2 border-t border-slate-100">
                <label class="text-[11px] text-slate-400">من</label>
                <input wire:model.live="dateDebut" type="date" class="form-field !h-7 !text-[11px] w-auto">
                <label class="text-[11px] text-slate-400">إلى</label>
                <input wire:model.live="dateFin" type="date" class="form-field !h-7 !text-[11px] w-auto">
            </div>
        @endif
    </div>

    {{-- ═══ Actions groupées (contextuel) ═══ --}}
    @if(count($selectionCommandes) > 0)
        <div class="flex items-center gap-2 rounded-lg bg-blue-50 border border-blue-200 px-3 py-2">
            <span class="text-xs font-medium text-blue-800">
                <span class="num-ltr">{{ count($selectionCommandes) }}</span> محدد
            </span>
            <select wire:model.live="statutGroupe" class="form-field !h-7 !text-[11px] max-w-[180px]">
                <option value="">اختر الإجراء</option>
                <option value="pret">تحويل إلى جاهز</option>
            </select>
            <button wire:click="appliquerChangementStatutGroupe" class="btn-primary !py-1 !text-[11px]" @disabled(empty($statutGroupe))>
                تطبيق
            </button>
            <button wire:click="$set('selectionCommandes', [])" class="text-[11px] text-blue-600 hover:text-blue-800 mr-auto">إلغاء التحديد</button>
        </div>
    @endif

    {{-- ═══ Contenu principal ═══ --}}
    <div class="grid lg:grid-cols-[1fr_340px] gap-3">

        {{-- ═══ Liste des commandes ═══ --}}
        <div class="card overflow-hidden">
            <table class="table-base w-full">
                <thead class="table-head">
                    <tr>
                        <th class="table-th w-8 text-center">
                            <input type="checkbox" wire:model.live="selectionPage" class="rounded border-slate-300 h-3.5 w-3.5">
                        </th>
                        <th class="table-th">الطلب</th>
                        <th class="table-th hidden sm:table-cell">التاريخ</th>
                        <th class="table-th">المبلغ</th>
                        <th class="table-th">الحالة</th>
                        <th class="table-th w-16"></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($resultats as $item)
                        <tr wire:click="selectionnerCommande({{ $item->id }})"
                            class="table-row cursor-pointer transition {{ $commandeSelectionneeId === $item->id ? 'bg-blue-50 border-r-2 border-r-blue-500' : 'hover:bg-slate-50' }}">
                            <td class="table-td text-center" wire:click.stop>
                                <input type="checkbox" value="{{ $item->id }}" wire:model.live="selectionCommandes" class="rounded border-slate-300 h-3.5 w-3.5">
                            </td>
                            <td class="table-td">
                                <div class="text-xs font-semibold text-slate-800 num-ltr">{{ $item->numero_commande }}</div>
                                <div class="text-[11px] text-slate-500">{{ $item->client?->full_name }}</div>
                            </td>
                            <td class="table-td hidden sm:table-cell">
                                <span class="text-[11px] text-slate-500 num-ltr">{{ $item->date_depot?->format('d/m') }}</span>
                            </td>
                            <td class="table-td">
                                <div class="text-xs font-semibold num-ltr {{ (float) $item->reste_a_payer > 0 ? 'text-red-600' : 'text-emerald-600' }}">
                                    {{ number_format((float) $item->montant_total, 0) }}
                                </div>
                                @if((float) $item->reste_a_payer > 0)
                                    <div class="text-[10px] text-red-400 num-ltr">باقي {{ number_format((float) $item->reste_a_payer, 0) }}</div>
                                @endif
                            </td>
                            <td class="table-td">
                                @php
                                    $statutStyles = [
                                        'en_cours' => 'bg-amber-100 text-amber-700',
                                        'pret' => 'bg-blue-100 text-blue-700',
                                        'livre' => 'bg-emerald-100 text-emerald-700',
                                    ];
                                @endphp
                                <span class="inline-flex rounded-full px-2 py-0.5 text-[10px] font-medium {{ $statutStyles[$item->statut] ?? 'bg-slate-100 text-slate-600' }}">
                                    {{ $item->statut_label }}
                                </span>
                            </td>
                            <td class="table-td text-center" wire:click.stop>
                                @hasanyrole(['gerant', 'المسير'])
                                    <button wire:click="demanderSuppressionCommande({{ $item->id }})" class="text-red-400 hover:text-red-600" title="حذف">
                                        <i class="fi fi-rr-trash text-[11px]"></i>
                                    </button>
                                @endhasanyrole
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="table-td text-center text-slate-400 py-8">
                            <i class="fi fi-rr-box-open text-2xl mb-1 block"></i>
                            <span class="text-xs">لا توجد نتائج.</span>
                        </td></tr>
                    @endforelse
                </tbody>
            </table>
            <div class="px-3 py-2 border-t border-slate-100 bg-slate-50/50">
                {{ $resultats->links() }}
            </div>
        </div>

        {{-- ═══ Panneau détail ═══ --}}
        <div class="space-y-3">
            @if($commande)
                {{-- En-tête détail --}}
                <div class="card card-body !p-3">
                    <div class="flex items-start justify-between mb-2">
                        <div>
                            <div class="text-sm font-bold text-slate-900 num-ltr">{{ $commande->numero_commande }}</div>
                            <div class="text-[11px] text-slate-500">{{ $commande->client?->full_name }} &middot; <span class="num-ltr">{{ $commande->client?->telephone }}</span></div>
                        </div>
                        @php
                            $detailStatutStyles = [
                                'en_cours' => 'bg-amber-100 text-amber-700',
                                'pret' => 'bg-blue-100 text-blue-700',
                                'livre' => 'bg-emerald-100 text-emerald-700',
                            ];
                        @endphp
                        <span class="inline-flex rounded-full px-2.5 py-0.5 text-[11px] font-medium {{ $detailStatutStyles[$commande->statut] ?? 'bg-slate-100 text-slate-600' }}">
                            {{ $commande->statut_label }}
                        </span>
                    </div>

                    {{-- Montants en grille --}}
                    <div class="grid grid-cols-2 gap-1.5">
                        <div class="rounded-md bg-slate-50 px-2.5 py-1.5">
                            <div class="text-[10px] text-slate-400">الإجمالي</div>
                            <div class="text-xs font-bold text-slate-800 num-ltr">{{ number_format((float) $commande->montant_total, 0) }} MRU</div>
                        </div>
                        <div class="rounded-md bg-slate-50 px-2.5 py-1.5">
                            <div class="text-[10px] text-slate-400">المدفوع</div>
                            <div class="text-xs font-bold text-emerald-700 num-ltr">{{ number_format((float) $commande->montant_paye, 0) }} MRU</div>
                        </div>
                        @if((float) $commande->total_remise > 0)
                            <div class="rounded-md bg-purple-50 px-2.5 py-1.5">
                                <div class="text-[10px] text-purple-400">الخصم</div>
                                <div class="text-xs font-bold text-purple-700 num-ltr">-{{ number_format((float) $commande->total_remise, 0) }} MRU</div>
                            </div>
                        @endif
                        @if((float) $commande->reste_a_payer > 0)
                            <div class="rounded-md bg-red-50 px-2.5 py-1.5">
                                <div class="text-[10px] text-red-400">المتبقي</div>
                                <div class="text-xs font-bold text-red-700 num-ltr">{{ number_format((float) $commande->reste_a_payer, 0) }} MRU</div>
                            </div>
                        @endif
                    </div>
                </div>

                {{-- Détails articles --}}
                <div class="card overflow-hidden">
                    <div class="px-3 py-1.5 border-b border-slate-100 bg-slate-50/50">
                        <span class="text-[11px] font-medium text-slate-600">تفاصيل القطع</span>
                    </div>
                    <table class="table-base w-full">
                        <thead class="table-head">
                            <tr>
                                <th class="table-th">الخدمة</th>
                                <th class="table-th text-center">الكمية</th>
                                <th class="table-th">الإجمالي</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($commande->details as $detail)
                                <tr class="table-row">
                                    <td class="table-td text-xs">{{ $detail->service?->libelle_ar ?: '-' }}</td>
                                    <td class="table-td text-center"><span class="num-ltr text-xs">{{ (int) $detail->quantite }}</span></td>
                                    <td class="table-td"><span class="num-ltr text-xs font-medium">{{ number_format((float) $detail->sous_total, 0) }}</span></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                {{-- Actions --}}
                <div class="flex flex-wrap gap-1.5">
                    @if((float) $commande->reste_a_payer > 0)
                        <button wire:click="ouvrirPaiement" class="btn-primary flex-1" wire:loading.attr="disabled">
                            <i class="fi fi-rr-coins mr-1"></i> تحصيل الباقي
                        </button>
                    @endif
                    @if($commande->statut === 'en_cours')
                        <button wire:click="confirmerChangementStatut({{ $commande->id }}, 'pret')" class="btn-secondary flex-1">
                            <i class="fi fi-rr-check mr-1"></i> جاهز
                        </button>
                    @endif
                    @if($commande->statut === 'pret')
                        <button wire:click="confirmerChangementStatut({{ $commande->id }}, 'livre')" class="btn-primary flex-1">
                            <i class="fi fi-rr-box-check mr-1"></i> مسلّم
                        </button>
                    @endif
                    <a href="{{ route('commandes.ticket', $commande) }}" target="_blank" class="btn-secondary">
                        <i class="fi fi-rr-print mr-1"></i> وصل
                    </a>
                    @hasanyrole(['gerant', 'المسير'])
                        <button wire:click="demanderSuppressionCommande({{ $commande->id }})" class="btn-danger">
                            <i class="fi fi-rr-trash mr-1"></i> حذف
                        </button>
                    @endhasanyrole
                </div>
            @else
                <div class="card card-body flex flex-col items-center justify-center py-12 text-slate-300">
                    <i class="fi fi-rr-cursor-finger text-3xl mb-2"></i>
                    <p class="text-xs text-slate-400">اختر طلبًا لعرض التفاصيل</p>
                </div>
            @endif
        </div>
    </div>

    {{-- ═══ Modale paiement ═══ --}}
    @if($afficherPaiement && $commande)
        <div class="modal-overlay flex items-center justify-center p-4">
            <div class="modal-panel max-w-sm p-4 space-y-3">
                <div class="flex items-center justify-between">
                    <h3 class="text-sm font-semibold text-slate-900">تحصيل المبلغ المتبقي</h3>
                    <button wire:click="$set('afficherPaiement', false)" class="text-slate-400 hover:text-slate-600">
                        <i class="fi fi-rr-cross-small text-sm"></i>
                    </button>
                </div>

                <div class="rounded-md bg-amber-50 border border-amber-200 px-3 py-2 text-center">
                    <div class="text-[10px] text-amber-600">المتبقي</div>
                    <div class="text-base font-bold text-amber-800 num-ltr">{{ number_format((float) $commande->reste_a_payer, 0) }} MRU</div>
                </div>

                <div class="space-y-2">
                    <div>
                        <label class="form-label">المبلغ المدفوع</label>
                        <input type="number" step="0.01" min="0" wire:model="montantAPayer" class="form-field">
                        @error('montantAPayer') <div class="form-error">{{ $message }}</div> @enderror
                    </div>
                    <div>
                        <label class="form-label">خصم عند التسوية (%)</label>
                        <input type="number" step="0.01" min="0" max="100" wire:model="remisePourcentage" class="form-field">
                        @if((float) $this->remisePourcentage > 0)
                            <div class="mt-1 text-[11px] text-slate-500">
                                خصم: <span class="num-ltr font-medium">{{ number_format((float) $this->remise_montant, 0) }} MRU</span>
                                &middot; المتبقي: <span class="num-ltr font-medium">{{ number_format((float) $this->reste_apres_remise, 0) }} MRU</span>
                            </div>
                        @endif
                        @error('remisePourcentage') <div class="form-error">{{ $message }}</div> @enderror
                    </div>
                    <div>
                        <label class="form-label">طريقة الدفع</label>
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
                </div>

                <div class="flex justify-end gap-2 pt-1">
                    <button wire:click="$set('afficherPaiement', false)" class="btn-secondary">إلغاء</button>
                    <button wire:click="encaisserReste" class="btn-primary" wire:loading.attr="disabled">تأكيد الدفع</button>
                </div>
            </div>
        </div>
    @endif

    {{-- ═══ Modale confirmation statut ═══ --}}
    @if($afficherConfirmationStatut)
        <div class="modal-overlay flex items-center justify-center p-4">
            <div class="modal-panel max-w-xs p-4 space-y-3">
                <h3 class="text-sm font-semibold text-slate-900">تأكيد تغيير الحالة</h3>
                <p class="text-xs text-slate-600">
                    @if($statutAConfirmer === 'pret')
                        هل تريد تحديد الطلب كـ <strong class="text-blue-700">جاهز</strong>؟
                    @elseif($statutAConfirmer === 'livre')
                        هل تريد تحديد الطلب كـ <strong class="text-emerald-700">مسلّم</strong>؟
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

    {{-- ═══ Modale suppression ═══ --}}
    @if($afficherConfirmationSuppression)
        <div class="modal-overlay flex items-center justify-center p-4">
            <div class="modal-panel max-w-xs p-4 space-y-3">
                <h3 class="text-sm font-semibold text-red-700">تأكيد حذف الطلب</h3>
                <p class="text-xs text-slate-600">
                    هل تريد حذف الطلب <strong class="num-ltr">{{ $numeroCommandeASupprimer }}</strong> نهائيًا؟
                </p>
                <div class="flex justify-end gap-2">
                    <button wire:click="annulerSuppressionCommande" class="btn-secondary">إلغاء</button>
                    <button wire:click="confirmerSuppressionCommande" class="btn-danger">حذف نهائي</button>
                </div>
            </div>
        </div>
    @endif

    {{-- ═══ Modale rappels ═══ --}}
    @if($afficherRappelsModal)
        <div class="modal-overlay flex items-center justify-center p-4">
            <div class="modal-panel max-w-4xl p-4 space-y-3">
                <div class="flex items-center justify-between gap-2">
                    <div class="flex items-center gap-2">
                        <i class="fi fi-rr-bell-ring text-amber-600"></i>
                        <h3 class="text-sm font-semibold text-amber-800">طلبات بحاجة للتذكير (أكثر من 7 أيام)</h3>
                        <span class="inline-flex items-center rounded-full bg-amber-100 px-2.5 py-1 text-xs font-medium text-amber-700">
                            <span class="num-ltr">{{ $commandesARappeler->count() }}</span>&nbsp;طلب
                        </span>
                    </div>
                    <button wire:click="fermerRappelsModal" class="btn-secondary">إغلاق</button>
                </div>

                <div class="table-wrap max-h-[60vh] overflow-y-auto">
                    <table class="table-base w-full">
                        <thead class="table-head">
                            <tr>
                                <th class="table-th">الطلب</th>
                                <th class="table-th">الزبون</th>
                                <th class="table-th">الهاتف</th>
                                <th class="table-th">منذ</th>
                                <th class="table-th text-right">إجراء</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($commandesARappeler as $commandeRappel)
                                <tr class="table-row">
                                    <td class="table-td num-ltr">{{ $commandeRappel->numero_commande }}</td>
                                    <td class="table-td">{{ $commandeRappel->client?->full_name ?? '-' }}</td>
                                    <td class="table-td num-ltr">{{ $commandeRappel->client?->telephone ?? '-' }}</td>
                                    <td class="table-td">
                                        <span class="inline-flex rounded-full bg-rose-100 px-2 py-0.5 text-[10px] font-medium text-rose-700">
                                            <span class="num-ltr">{{ max(7, (int) $commandeRappel->date_depot?->diffInDays(now())) }}</span>&nbsp;يوم
                                        </span>
                                    </td>
                                    <td class="table-td text-right">
                                        <div class="inline-flex items-center gap-2">
                                            <button wire:click="ouvrirCommandeDepuisRappel({{ $commandeRappel->id }})" class="btn-ghost !px-2.5 !py-1.5 !text-xs text-blue-700">
                                                <i class="fi fi-rr-eye mr-1"></i> فتح
                                            </button>
                                            @if($commandeRappel->client?->telephone)
                                                <a href="tel:{{ preg_replace('/\D+/', '', $commandeRappel->client->telephone) }}" class="btn-ghost !px-2.5 !py-1.5 !text-xs text-emerald-700">
                                                    <i class="fi fi-rr-phone-call mr-1"></i> اتصال
                                                </a>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="table-td text-center text-slate-500">لا توجد طلبات بحاجة للتذكير.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endif
</div>
