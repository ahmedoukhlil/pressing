<div class="page-container space-y-3">
    <div class="page-header">
        <div>
            <h1 class="page-title">الطلبات</h1>
            <p class="page-subtitle">بحث ومتابعة الحالة وتحصيل الباقي.</p>
        </div>
        <div class="flex flex-wrap items-center gap-2">
            <button
                type="button"
                wire:click="ouvrirRappelsModal"
                class="btn-secondary relative !py-2 !px-3"
            >
                <i class="fi fi-rr-bell-ring text-sm opacity-80 shrink-0"></i>
                <span>تذكيرات</span>
                <span class="ms-0.5 inline-flex h-5 min-w-[1.25rem] items-center justify-center rounded-full bg-amber-500 px-1.5 text-[10px] font-bold text-white shadow-sm num-ltr tabular-nums">
                    {{ $commandesARappeler->count() }}
                </span>
            </button>
            <a href="{{ route('exports.commandes.pdf', array_filter([
                'date_debut' => $dateDebut ?: now()->toDateString(),
                'date_fin'   => $dateFin   ?: now()->toDateString(),
                'statut'     => $filtreStatut,
                'recherche'  => $recherche,
            ])) }}" class="btn-secondary !py-2 !px-3">
                <i class="fi fi-rr-file-pdf text-sm text-red-600/90 shrink-0"></i>
                <span>تصدير PDF</span>
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

            <div class="flex items-center gap-0.5 rounded-lg border border-slate-200/90 bg-slate-50/90 p-1 shadow-inner">
                <button wire:click="$set('filtreStatut', '')" type="button"
                    class="rounded-md px-2.5 py-1.5 text-[11px] font-semibold transition duration-150 active:scale-[0.97] {{ $filtreStatut === '' ? 'bg-slate-800 text-white shadow-sm' : 'text-slate-600 hover:bg-white hover:text-slate-800 hover:shadow-sm' }}">
                    الكل
                </button>
                <button wire:click="$set('filtreStatut', 'en_cours')" type="button"
                    class="rounded-md px-2.5 py-1.5 text-[11px] font-semibold transition duration-150 active:scale-[0.97] {{ $filtreStatut === 'en_cours' ? 'bg-amber-500 text-white shadow-sm shadow-amber-600/30' : 'text-slate-600 hover:bg-white hover:text-slate-800 hover:shadow-sm' }}">
                    قيد المعالجة
                </button>
                <button wire:click="$set('filtreStatut', 'pret')" type="button"
                    class="rounded-md px-2.5 py-1.5 text-[11px] font-semibold transition duration-150 active:scale-[0.97] {{ $filtreStatut === 'pret' ? 'bg-sky-600 text-white shadow-sm shadow-sky-900/25' : 'text-slate-600 hover:bg-white hover:text-slate-800 hover:shadow-sm' }}">
                    جاهز
                </button>
                <button wire:click="$set('filtreStatut', 'livre')" type="button"
                    class="rounded-md px-2.5 py-1.5 text-[11px] font-semibold transition duration-150 active:scale-[0.97] {{ $filtreStatut === 'livre' ? 'bg-emerald-600 text-white shadow-sm shadow-emerald-900/25' : 'text-slate-600 hover:bg-white hover:text-slate-800 hover:shadow-sm' }}">
                    مسلّم
                </button>
            </div>

            <button type="button" wire:click="$toggle('afficherFiltresAvances')" class="btn-ghost !px-2.5 !py-1.5 !text-[11px] text-slate-600 border border-transparent hover:border-slate-200">
                <i class="fi fi-rr-calendar text-sm opacity-70"></i>
                <span>التاريخ</span>
            </button>

            @if($recherche !== '' || $filtreStatut !== '' || $dateDebut !== '' || $dateFin !== '')
                <button type="button" wire:click="reinitialiserFiltres" class="btn-ghost !px-2.5 !py-1.5 !text-[11px] text-red-600 hover:bg-red-50 hover:text-red-700">
                    <i class="fi fi-rr-cross-small text-sm"></i>
                    <span>مسح</span>
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
        <div class="flex flex-wrap items-center gap-2 rounded-xl bg-gradient-to-l from-blue-50 to-indigo-50/80 border border-blue-200/80 px-3 py-2.5 shadow-sm">
            <span class="inline-flex items-center gap-1 rounded-lg bg-white/80 px-2 py-1 text-xs font-semibold text-blue-900 shadow-sm">
                <span class="num-ltr tabular-nums text-blue-700">{{ count($selectionCommandes) }}</span>
                <span>محدد</span>
            </span>
            <select wire:model.live="statutGroupe" class="form-field !h-9 !text-[11px] max-w-[200px] rounded-lg border-blue-200/80 font-medium shadow-sm">
                <option value="">اختر الإجراء</option>
                <option value="pret">تحويل إلى جاهز</option>
            </select>
            <button type="button" wire:click="appliquerChangementStatutGroupe" class="btn-primary !py-2 !px-4 shadow-md shadow-blue-900/20" @disabled(empty($statutGroupe))>
                <i class="fi fi-rr-check text-sm"></i>
                <span>تطبيق</span>
            </button>
            <button type="button" wire:click="$set('selectionCommandes', [])" class="btn-ghost !py-2 !text-[11px] text-blue-700 hover:bg-blue-100/60 ms-auto">
                إلغاء التحديد
            </button>
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
                                    <button type="button" wire:click="demanderSuppressionCommande({{ $item->id }})" class="inline-flex h-8 w-8 items-center justify-center rounded-lg text-red-500 transition hover:bg-red-50 hover:text-red-700 active:scale-95 focus:outline-none focus-visible:ring-2 focus-visible:ring-red-300" title="حذف">
                                        <i class="fi fi-rr-trash text-xs"></i>
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
                        <div class="flex flex-col items-end gap-0.5">
                            <span class="inline-flex rounded-full px-2.5 py-0.5 text-[11px] font-medium {{ $detailStatutStyles[$commande->statut] ?? 'bg-slate-100 text-slate-600' }}">
                                {{ $commande->statut_label }}
                            </span>
                            @if($commande->remise_partielle_en_cours)
                                <span class="text-[10px] font-medium text-violet-600">تسليم تدريجي</span>
                            @endif
                        </div>
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

                {{-- Détails articles (liste cartes — adaptée au panneau étroit) --}}
                <div class="card overflow-hidden shadow-sm border-slate-200/80">
                    <div class="px-3 py-2.5 border-b border-slate-100 bg-gradient-to-l from-slate-50/90 to-white">
                        <div class="flex items-start gap-2.5">
                            <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl border border-slate-200/80 bg-white shadow-sm text-slate-500">
                                <i class="fi fi-rr-receipt text-sm"></i>
                            </span>
                            <div class="min-w-0 flex-1 space-y-1">
                                <h3 class="text-xs font-semibold text-slate-800 leading-tight">تفاصيل القطع</h3>
                                <details class="group text-[10px] text-slate-500 leading-relaxed">
                                    <summary class="cursor-pointer list-none text-indigo-600 hover:text-indigo-800 [&::-webkit-details-marker]:hidden flex items-center gap-0.5">
                                        <i class="fi fi-rr-info text-[9px] opacity-80"></i>
                                        <span>كيفية التسليم التدريجي</span>
                                        <span class="text-[8px] text-indigo-400 transition group-open:rotate-180 inline-block">▼</span>
                                    </summary>
                                    <p class="mt-1.5 ps-1 text-slate-500 border-s border-slate-200/80 pe-0.5">
                                        عيّن كل قطعة جاهزة، ثم سجّل الكمية المسلّمة عند مجيء الزبون. يُغلق الطلب تلقائيًا بعد اكتمال كل الكميات.
                                    </p>
                                </details>
                            </div>
                        </div>
                    </div>
                    <ul class="divide-y divide-slate-100 max-h-[min(52vh,28rem)] overflow-y-auto overscroll-contain">
                        @foreach($commande->details as $detail)
                            @php
                                $qRendue = (int) $detail->quantite_rendue;
                                $qTot = (int) $detail->quantite;
                                $restant = max(0, $qTot - $qRendue);
                                $pct = $qTot > 0 ? (int) round(min(100, ($qRendue / $qTot) * 100)) : 0;
                                $badgeDone = $qRendue >= $qTot;
                                $badgeClass = $badgeDone
                                    ? 'bg-emerald-50 text-emerald-800 ring-1 ring-emerald-200/70'
                                    : ($detail->statut_ligne === 'pret'
                                        ? 'bg-sky-50 text-sky-900 ring-1 ring-sky-200/80'
                                        : 'bg-amber-50 text-amber-900 ring-1 ring-amber-200/70');
                            @endphp
                            <li wire:key="detail-{{ $detail->id }}" class="px-3 py-3 transition-colors hover:bg-slate-50/60">
                                <div class="flex items-start justify-between gap-2 mb-2">
                                    <div class="min-w-0 flex-1">
                                        <p class="text-xs font-semibold text-slate-900 leading-snug">{{ $detail->service?->libelle_ar ?: '—' }}</p>
                                        @if($detail->notes)
                                            <p class="text-[10px] text-slate-500 mt-0.5 line-clamp-2">{{ $detail->notes }}</p>
                                        @endif
                                    </div>
                                    <span class="inline-flex shrink-0 items-center rounded-lg px-2 py-0.5 text-[10px] font-medium {{ $badgeClass }}">
                                        {{ $detail->statut_ligne_label }}
                                    </span>
                                </div>

                                <div class="rounded-lg bg-slate-50/90 border border-slate-100 px-2.5 py-2 space-y-1.5">
                                    <div class="flex items-center justify-between gap-2 text-[10px]">
                                        <span class="text-slate-500">التسليم</span>
                                        <span class="num-ltr font-semibold tabular-nums {{ $badgeDone ? 'text-emerald-700' : 'text-slate-800' }}">
                                            {{ $qRendue }} <span class="text-slate-400 font-normal">/</span> {{ $qTot }}
                                        </span>
                                    </div>
                                    <div class="h-2 rounded-full bg-slate-200/90 overflow-hidden" role="progressbar" aria-valuenow="{{ $pct }}" aria-valuemin="0" aria-valuemax="100">
                                        <div
                                            class="h-full rounded-full transition-all duration-300 {{ $badgeDone ? 'bg-emerald-500' : 'bg-sky-500' }}"
                                            style="width: {{ $pct }}%"
                                        ></div>
                                    </div>
                                </div>

                                <div class="mt-2.5 space-y-2">
                                    @if($detail->statut_ligne === 'en_cours' && $qRendue < $qTot)
                                        <button
                                            type="button"
                                            wire:click="marquerLignePret({{ $detail->id }})"
                                            class="btn-success w-full !py-2.5 !text-[11px]"
                                            wire:loading.attr="disabled"
                                        >
                                            <i class="fi fi-rr-check text-sm"></i>
                                            <span>جاهز للتسليم</span>
                                        </button>
                                    @endif

                                    @if($detail->statut_ligne === 'pret' && $restant > 0)
                                        <div class="rounded-xl border border-slate-200/90 bg-white p-2.5 space-y-2 shadow-sm ring-1 ring-slate-900/[0.03]">
                                            <label class="flex items-center justify-between gap-2 text-[10px] font-medium text-slate-600">
                                                <span>كمية التسليم</span>
                                                <span class="num-ltr rounded-md bg-amber-50 px-1.5 py-0.5 text-amber-800 tabular-nums ring-1 ring-amber-200/60">متبقي {{ $restant }}</span>
                                            </label>
                                            <div class="flex items-stretch gap-2">
                                                <input
                                                    type="number"
                                                    min="1"
                                                    max="{{ $restant }}"
                                                    inputmode="numeric"
                                                    class="form-field min-w-0 flex-1 !h-10 !rounded-lg !text-sm !py-2 num-ltr text-center font-bold tabular-nums shadow-inner"
                                                    wire:model.live="remisePartielle.{{ $detail->id }}"
                                                >
                                                <button
                                                    type="button"
                                                    wire:click="enregistrerRemiseLigne({{ $detail->id }})"
                                                    class="btn-primary !h-10 shrink-0 !rounded-lg !px-4 !py-0 !text-[11px] shadow-md shadow-blue-900/15"
                                                    wire:loading.attr="disabled"
                                                >
                                                    <i class="fi fi-rr-box-check text-sm"></i>
                                                    <span>تسليم</span>
                                                </button>
                                            </div>
                                        </div>
                                    @endif

                                    @if($qRendue >= $qTot)
                                        <div class="flex items-center justify-center gap-1.5 rounded-lg bg-emerald-50/90 border border-emerald-100 py-2 text-[11px] font-medium text-emerald-800">
                                            <span class="flex h-5 w-5 items-center justify-center rounded-full bg-emerald-500 text-white text-[10px]">✓</span>
                                            مكتمل التسليم
                                        </div>
                                    @endif
                                </div>
                            </li>
                        @endforeach
                    </ul>
                </div>

                {{-- Actions commande --}}
                <div class="rounded-xl border border-slate-200/90 bg-gradient-to-b from-white to-slate-50/90 p-2.5 shadow-sm space-y-2">
                    <div class="grid grid-cols-1 gap-2">
                        @if((float) $commande->reste_a_payer > 0)
                            <button type="button" wire:click="ouvrirPaiement" class="btn-primary w-full justify-center !py-2.5 shadow-md shadow-blue-900/20" wire:loading.attr="disabled">
                                <i class="fi fi-rr-coins text-base opacity-90"></i>
                                <span>تحصيل الباقي</span>
                            </button>
                        @endif
                        @if($commande->statut === 'en_cours')
                            <button type="button" wire:click="confirmerChangementStatut({{ $commande->id }}, 'pret')" class="btn-secondary w-full justify-center !py-2.5 border-sky-200/80 bg-sky-50/50 text-sky-950 hover:bg-sky-50">
                                <i class="fi fi-rr-check text-sky-700 text-base"></i>
                                <span>كل القطع جاهزة</span>
                            </button>
                        @endif
                        @if($commande->statut === 'pret')
                            @php
                                $toutesLignesRemises = $commande->details->every(fn ($d) => (int) $d->quantite_rendue >= (int) $d->quantite);
                            @endphp
                            @if($toutesLignesRemises)
                                <button type="button" wire:click="confirmerChangementStatut({{ $commande->id }}, 'livre')" class="btn-primary w-full justify-center !py-2.5 !bg-emerald-600 hover:!bg-emerald-700 shadow-md shadow-emerald-900/20">
                                    <i class="fi fi-rr-box-check text-base"></i>
                                    <span>إغلاق الطلب (مسلّم)</span>
                                </button>
                            @else
                                <div class="rounded-lg border border-dashed border-slate-300 bg-slate-50 px-2.5 py-2 text-[10px] leading-relaxed text-slate-600">
                                    سجّل تسليم القطع أعلاه؛ سيُغلق الطلب تلقائيًا عند اكتمال الكميات.
                                </div>
                            @endif
                        @endif
                    </div>
                    <div class="flex flex-wrap gap-2">
                        <a href="{{ route('commandes.ticket', $commande) }}" target="_blank" rel="noopener noreferrer" class="btn-secondary flex-1 min-w-[6rem] justify-center !py-2">
                            <i class="fi fi-rr-print text-sm"></i>
                            <span>وصل</span>
                        </a>
                        @hasanyrole(['gerant', 'المسير'])
                            <button type="button" wire:click="demanderSuppressionCommande({{ $commande->id }})" class="btn-danger flex-1 min-w-[6rem] justify-center !py-2">
                                <i class="fi fi-rr-trash text-sm"></i>
                                <span>حذف</span>
                            </button>
                        @endhasanyrole
                    </div>
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
                    <button type="button" wire:click="$set('afficherPaiement', false)" class="btn-ghost !h-9 !w-9 !p-0 rounded-full text-slate-400 hover:bg-slate-100 hover:text-slate-700" aria-label="إغلاق">
                        <i class="fi fi-rr-cross-small text-base"></i>
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

                <div class="flex flex-col-reverse sm:flex-row justify-end gap-2 pt-1">
                    <button type="button" wire:click="$set('afficherPaiement', false)" class="btn-secondary w-full sm:w-auto !py-2.5">إلغاء</button>
                    <button type="button" wire:click="encaisserReste" class="btn-primary w-full sm:w-auto sm:min-w-[8rem] !py-2.5 shadow-lg shadow-blue-900/20" wire:loading.attr="disabled">
                        <i class="fi fi-rr-coins text-sm opacity-90"></i>
                        <span>تأكيد الدفع</span>
                    </button>
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
                        هل تريد إغلاق الطلب كـ <strong class="text-emerald-700">مسلّم</strong>؟ يجب أن تكون كل القطع قد سُلّمت للزبون.
                    @else
                        هل تريد تغيير حالة الطلب؟
                    @endif
                </p>
                <div class="flex flex-col-reverse sm:flex-row justify-end gap-2 pt-0.5">
                    <button type="button" wire:click="annulerConfirmationStatut" class="btn-secondary w-full sm:w-auto !py-2.5">إلغاء</button>
                    <button type="button" wire:click="validerChangementStatut" class="btn-primary w-full sm:w-auto !py-2.5 shadow-md shadow-blue-900/15">
                        <i class="fi fi-rr-check text-sm"></i>
                        <span>تأكيد</span>
                    </button>
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
                <div class="flex flex-col-reverse sm:flex-row justify-end gap-2 pt-0.5">
                    <button type="button" wire:click="annulerSuppressionCommande" class="btn-secondary w-full sm:w-auto !py-2.5">إلغاء</button>
                    <button type="button" wire:click="confirmerSuppressionCommande" class="btn-danger w-full sm:w-auto !py-2.5 shadow-md shadow-red-900/20">
                        <i class="fi fi-rr-trash text-sm"></i>
                        <span>حذف نهائي</span>
                    </button>
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
                    <button type="button" wire:click="fermerRappelsModal" class="btn-secondary shrink-0 !py-2 !px-3">
                        <i class="fi fi-rr-cross-small text-sm"></i>
                        <span>إغلاق</span>
                    </button>
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
                                            <button type="button" wire:click="ouvrirCommandeDepuisRappel({{ $commandeRappel->id }})" class="btn-secondary !border-blue-200/80 !bg-blue-50/80 !py-1.5 !px-2.5 !text-[11px] !text-blue-800 hover:!bg-blue-50">
                                                <i class="fi fi-rr-eye text-sm"></i>
                                                <span>فتح</span>
                                            </button>
                                            @if($commandeRappel->client?->telephone)
                                                <a href="tel:{{ preg_replace('/\D+/', '', $commandeRappel->client->telephone) }}" class="btn-secondary !border-emerald-200/80 !bg-emerald-50/80 !py-1.5 !px-2.5 !text-[11px] !text-emerald-900 hover:!bg-emerald-50">
                                                    <i class="fi fi-rr-phone-call text-sm"></i>
                                                    <span>اتصال</span>
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
