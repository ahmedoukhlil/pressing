@php
    $panierCount = count($panier);
    $quantiteParService = collect($panier)->pluck('quantite', 'service_id')->toArray();
    $totalArticles = (int) collect($panier)->sum('quantite');
@endphp

<div class="max-w-7xl mx-auto px-3 py-3 space-y-2">

    {{-- ═══ Barre compacte : Client + Résumé ═══ --}}
    <div class="flex flex-wrap items-center gap-2">
        @if($clientInfo)
            <div class="flex items-center gap-2 rounded-lg bg-emerald-50 border border-emerald-200 px-3 py-1.5">
                <div class="flex h-7 w-7 items-center justify-center rounded-full bg-emerald-600 text-white font-bold text-xs shrink-0">
                    {{ mb_substr($clientInfo['nom'], 0, 1) }}
                </div>
                <div class="text-sm">
                    <span class="font-semibold text-emerald-900">{{ $clientInfo['nom'] }}</span>
                    <span class="text-emerald-700 num-ltr text-xs mr-1">{{ $clientInfo['telephone'] }}</span>
                </div>
                <button wire:click="resetClient" class="text-emerald-500 hover:text-red-500 transition mr-1" title="تغيير">
                    <i class="fi fi-rr-cross-small text-sm"></i>
                </button>
            </div>
            @if($this->loyalty_settings['enabled'])
                <div class="flex items-center gap-1.5 rounded-lg border border-amber-200 bg-amber-50 px-2.5 py-1 text-xs text-amber-800">
                    <i class="fi fi-rr-star text-[11px]"></i>
                    <span>النقاط:</span>
                    <span class="font-bold num-ltr">{{ number_format((int) $soldePointsClient) }}</span>
                </div>
            @endif
        @else
            <div class="relative flex-1 max-w-sm">
                <input
                    type="tel"
                    inputmode="numeric"
                    maxlength="8"
                    wire:model.live.debounce.300ms="rechercheClient"
                    placeholder="ابحث برقم الهاتف..."
                    class="form-field !h-9 !text-sm pe-8 w-full"
                >
                <i class="fi fi-rr-search absolute top-1/2 -translate-y-1/2 end-2.5 text-slate-400 text-xs pointer-events-none"></i>
            </div>
        @endif

        @if($panierCount > 0)
            <div class="flex items-center gap-3 mr-auto text-xs text-slate-600">
                <span><strong class="text-slate-900 num-ltr">{{ $totalArticles }}</strong> قطعة</span>
                <span class="font-bold text-blue-700 num-ltr text-sm">{{ number_format((float) $this->montant_total_net, 0) }} MRU</span>
            </div>
        @endif

        @error('client') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
    </div>

    {{-- Résultats recherche client (dropdown compact) --}}
    @if(!$clientInfo && !empty($clientsTrouves))
        <div class="flex flex-wrap gap-1.5">
            @foreach($clientsTrouves as $c)
                <button
                    wire:click="selectionnerClient({{ $c['id'] }})"
                    class="flex items-center gap-2 rounded-lg border border-slate-200 px-2.5 py-1.5 text-xs hover:bg-blue-50 hover:border-blue-300 transition"
                >
                    <span class="font-medium text-slate-800">{{ $c['nom'] }}</span>
                    <span class="text-slate-400 num-ltr">{{ $c['telephone'] }}</span>
                </button>
            @endforeach
        </div>
    @endif

    <div class="grid lg:grid-cols-[1fr_280px] gap-3" style="height: calc(100vh - 160px);">

        {{-- ═══ Zone Services ═══ --}}
        <div class="overflow-y-auto scrollbar-hidden rounded-xl border border-slate-200 bg-white p-3">
            <div class="grid grid-cols-3 sm:grid-cols-4 md:grid-cols-5 lg:grid-cols-5 xl:grid-cols-6 gap-2.5">
                @forelse($services as $service)
                    @php $qteEnPanier = $quantiteParService[$service->id] ?? 0; @endphp
                    <button
                        wire:click="ajouterAuPanier({{ $service->id }})"
                        class="group relative flex flex-col items-center justify-center rounded-lg border p-3 text-center transition-all
                            {{ $qteEnPanier > 0
                                ? 'border-blue-400 bg-blue-50/80 shadow-sm'
                                : 'border-slate-200 bg-white hover:border-blue-300 hover:bg-slate-50' }}"
                    >
                        @if($qteEnPanier > 0)
                            <span class="absolute -top-2 -left-2 flex h-5 w-5 items-center justify-center rounded-full bg-blue-600 text-white text-[10px] font-bold shadow-sm z-10">
                                {{ $qteEnPanier }}
                            </span>
                        @endif
                        @if($service->image)
                            <img src="{{ Storage::url($service->image) }}" alt="{{ $service->libelle_ar }}" class="h-10 w-10 rounded-md object-cover">
                        @else
                            <span class="text-2xl leading-none">{{ $service->icone ?: '🧺' }}</span>
                        @endif
                        <span class="text-xs font-medium text-slate-800 leading-tight mt-1.5 line-clamp-1">{{ $service->libelle_ar ?: '-' }}</span>
                        <span class="text-[11px] text-slate-400 num-ltr mt-0.5">{{ number_format((float) $service->prix, 0) }} MRU</span>
                    </button>
                @empty
                    <div class="col-span-full flex flex-col items-center justify-center py-6 text-slate-400">
                        <i class="fi fi-rr-box-open text-2xl mb-1"></i>
                        <p class="text-xs">لا توجد خدمات.</p>
                    </div>
                @endforelse
            </div>
            @error('panier') <div class="form-error mt-2">{{ $message }}</div> @enderror
        </div>

        {{-- ═══ Panier (sidebar fixe) ═══ --}}
        <div class="flex flex-col rounded-xl border border-slate-200 bg-white overflow-hidden">
            {{-- Header panier --}}
            <div class="flex items-center justify-between px-3 py-2 border-b border-slate-100 bg-slate-50/80 shrink-0">
                <span class="text-sm font-semibold text-slate-800">
                    السلة
                    @if($panierCount > 0)
                        <span class="inline-flex items-center justify-center rounded-full bg-blue-600 text-white text-[10px] font-bold h-4 w-4 mr-0.5">{{ $panierCount }}</span>
                    @endif
                </span>
                @if($panierCount > 0)
                    <button wire:click="resetPanier" class="text-[10px] text-red-500 hover:text-red-700">إفراغ</button>
                @endif
            </div>

            {{-- Items --}}
            <div class="flex-1 overflow-y-auto scrollbar-hidden px-2.5 py-2 space-y-1.5">
                @forelse($panier as $i => $item)
                    <div class="rounded-lg border border-slate-100 bg-slate-50/50 px-2.5 py-2 space-y-1.5" wire:key="panier-{{ $i }}-{{ $item['quantite'] }}">
                        <div class="flex items-center justify-between gap-1">
                            <span class="text-xs font-semibold text-slate-800 truncate">{{ $item['icone'] }} {{ $item['libelle'] }}</span>
                            <span class="text-xs font-bold text-blue-700 num-ltr shrink-0">{{ number_format((float) $item['sous_total'], 0) }}</span>
                        </div>
                        <div class="flex items-center gap-1.5">
                            <div class="inline-flex items-center rounded border border-slate-300 bg-white h-7"
                                 x-data="{ qte: {{ (int) $item['quantite'] }} }">
                                <button type="button"
                                        @click="qte = Math.max(1, qte - 1); $wire.modifierQuantite({{ $i }}, qte)"
                                        class="w-6 h-full flex items-center justify-center text-slate-500 hover:text-slate-900 text-sm font-bold select-none">−</button>
                                <span class="w-5 text-center text-[11px] font-bold text-slate-800" x-text="qte"></span>
                                <button type="button"
                                        @click="qte++; $wire.modifierQuantite({{ $i }}, qte)"
                                        class="w-6 h-full flex items-center justify-center text-slate-500 hover:text-slate-900 text-sm font-bold select-none">+</button>
                            </div>
                            <input
                                type="text"
                                value="{{ $item['notes'] }}"
                                @change="$wire.modifierObservation({{ $i }}, $event.target.value)"
                                class="flex-1 h-7 rounded border-slate-200 text-[10px] px-1.5 min-w-0"
                                placeholder="ملاحظة"
                            >
                            <button wire:click="retirerDuPanier({{ $i }})" class="text-red-400 hover:text-red-600 shrink-0" title="حذف">
                                <i class="fi fi-rr-trash text-[11px]"></i>
                            </button>
                        </div>
                    </div>
                @empty
                    <div class="flex flex-col items-center justify-center h-full text-slate-300 py-6">
                        <i class="fi fi-rr-shopping-cart text-2xl mb-1"></i>
                        <p class="text-[11px]">السلة فارغة</p>
                    </div>
                @endforelse
            </div>

            {{-- Footer panier --}}
            @if($panierCount > 0)
                <div class="shrink-0 border-t border-slate-200 px-3 py-2.5 space-y-2 bg-slate-50/80">
                    {{-- Remise compacte --}}
                    <div class="flex items-center gap-2 text-xs">
                        <span class="text-slate-500">خصم</span>
                        <input type="number" min="0" max="100" step="1" wire:model.live="remisePourcentage" class="h-6 w-14 rounded border-slate-300 text-[11px] text-center px-1">
                        <span class="text-slate-400">%</span>
                        @if((float) $remisePourcentage > 0)
                            <span class="text-slate-500 num-ltr mr-auto">-{{ number_format((float) $this->remise_montant, 0) }}</span>
                        @endif
                    </div>

                    {{-- Total --}}
                    <div class="flex items-center justify-between">
                        <span class="text-sm font-bold text-slate-900">الإجمالي</span>
                        <span class="text-lg font-bold text-blue-700 num-ltr">{{ number_format((float) $this->montant_total_net, 0) }} MRU</span>
                    </div>

                    {{-- Bouton --}}
                    <button
                        wire:click="ouvrirModalPaiement"
                        class="w-full btn-primary !py-2.5 !text-sm"
                        wire:loading.attr="disabled"
                        @disabled(!$clientSelectionneId)
                    >
                        متابعة للدفع
                    </button>
                    @if(!$clientSelectionneId)
                        <p class="text-center text-[10px] text-amber-600">اختر الزبون أولاً</p>
                    @endif
                </div>
            @endif
        </div>
    </div>

    {{-- ═══ Modale Paiement ═══ --}}
    @if($afficherModalPaiement)
        <div class="modal-overlay flex items-center justify-center p-4">
            <div class="modal-panel max-w-md p-4 space-y-3">
                <div class="flex items-center justify-between">
                    <h3 class="text-base font-semibold text-slate-900">الدفع عند الإيداع</h3>
                    <button wire:click="fermerModalPaiement" class="rounded-md p-1 text-slate-400 hover:bg-slate-100 hover:text-slate-700">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
                    </button>
                </div>

                <div class="rounded-lg bg-blue-50 border border-blue-200 px-3 py-2 text-xs text-blue-700">
                    الدفع اختياري. اتركه 0 للتسجيل بدون دفع.
                </div>

                @if($this->loyalty_settings['enabled'])
                    <div class="rounded-lg bg-amber-50 border border-amber-200 px-3 py-2 space-y-2">
                        <div class="flex items-center justify-between text-xs text-amber-800">
                            <span>رصيد النقاط الحالي</span>
                            <span class="font-bold num-ltr">{{ number_format((int) $soldePointsClient) }}</span>
                        </div>
                        <div>
                            <label class="form-label !mb-1 text-amber-900">النقاط المراد استخدامها</label>
                            <input type="number" min="0" step="1" wire:model.live="pointsAUtiliser" class="form-field font-bold">
                            <p class="mt-1 text-[11px] text-amber-700">
                                الخصم بالنقاط:
                                <span class="font-semibold num-ltr">{{ number_format((float) $this->remise_points_montant, 0) }} MRU</span>
                            </p>
                            @error('pointsAUtiliser') <div class="form-error">{{ $message }}</div> @enderror
                        </div>
                    </div>
                @endif

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="form-label">المبلغ المدفوع</label>
                        <input type="number" step="0.01" min="0" wire:model.live="montantPaye" class="form-field font-bold">
                        @error('montantPaye') <div class="form-error">{{ $message }}</div> @enderror
                    </div>
                    <div>
                        <label class="form-label">طريقة الدفع</label>
                        <select wire:model="modeReglement" class="form-field">
                            @forelse($modesPaiement as $mode)
                                <option
                                    value="{{ $mode->code }}"
                                    @if($mode->code === 'non_paye' && (float) $montantPaye > 0) disabled @endif
                                >
                                    {{ $mode->icone ? $mode->icone . ' ' : '' }}{{ $mode->libelle }}
                                </option>
                            @empty
                                <option value="especes">نقدًا</option>
                                <option value="carte">بطاقة</option>
                                <option value="virement">تحويل</option>
                                <option value="non_paye" @disabled((float) $montantPaye > 0)>غير مدفوع</option>
                            @endforelse
                        </select>
                        @error('modeReglement') <div class="form-error">{{ $message }}</div> @enderror
                    </div>
                </div>

                <div>
                    <label class="form-label">ملاحظات</label>
                    <textarea wire:model="notes" class="form-field" rows="1" placeholder="اختياري"></textarea>
                </div>

                <div class="flex items-center justify-between rounded-lg bg-amber-50 border border-amber-200 px-3 py-2 text-sm">
                    <span class="text-amber-800">المتبقي</span>
                    <span class="font-bold text-amber-900 num-ltr">{{ number_format((float) $this->reste_a_payer, 2, ',', ' ') }} MRU</span>
                </div>

                <div class="flex justify-end gap-2">
                    <button wire:click="fermerModalPaiement" class="btn-secondary">إلغاء</button>
                    <button wire:click="confirmerCommande" class="btn-primary" wire:loading.attr="disabled">متابعة للتأكيد</button>
                </div>
            </div>
        </div>
    @endif

    {{-- ═══ Modale Confirmation ═══ --}}
    @if($afficherModalConfirmation && $clientInfo)
        <div class="modal-overlay flex items-center justify-center p-4">
            <div class="modal-panel max-w-md p-4 space-y-3">
                <h3 class="text-base font-semibold text-slate-900">تأكيد نهائي</h3>

                <div class="grid grid-cols-2 gap-2 text-sm">
                    <div class="rounded-lg border border-slate-200 p-2.5">
                        <div class="text-[10px] text-slate-500">الزبون</div>
                        <div class="font-semibold text-slate-800 text-xs">{{ $clientInfo['nom'] }}</div>
                    </div>
                    <div class="rounded-lg border border-slate-200 p-2.5">
                        <div class="text-[10px] text-slate-500">القطع</div>
                        <div class="font-bold text-slate-900 num-ltr">{{ $totalArticles }}</div>
                    </div>
                    <div class="rounded-lg border border-emerald-200 bg-emerald-50 p-2.5">
                        <div class="text-[10px] text-emerald-600">الإجمالي</div>
                        <div class="font-bold text-emerald-800 num-ltr">{{ number_format((float) $this->montant_total_apres_points, 2, ',', ' ') }} MRU</div>
                    </div>
                    <div class="rounded-lg border border-blue-200 bg-blue-50 p-2.5">
                        <div class="text-[10px] text-blue-600">المدفوع</div>
                        <div class="font-bold text-blue-800 num-ltr">{{ number_format((float) $montantPaye, 0) }} MRU</div>
                    </div>
                    @if((int) $this->points_a_utiliser_normalises > 0)
                        <div class="rounded-lg border border-purple-200 bg-purple-50 p-2.5 col-span-2">
                            <div class="flex justify-between items-center">
                                <span class="text-[10px] text-purple-700">نقاط مستعملة</span>
                                <span class="font-bold text-purple-800 num-ltr">
                                    {{ number_format((int) $this->points_a_utiliser_normalises) }} نقطة
                                    ({{ number_format((float) $this->remise_points_montant, 0) }} MRU)
                                </span>
                            </div>
                        </div>
                    @endif
                    <div class="rounded-lg border border-amber-200 bg-amber-50 p-2.5 col-span-2">
                        <div class="flex justify-between items-center">
                            <span class="text-[10px] text-amber-700">المتبقي</span>
                            <span class="font-bold text-amber-800 num-ltr">{{ number_format((float) $this->reste_a_payer, 2, ',', ' ') }} MRU</span>
                        </div>
                    </div>
                </div>

                <div class="flex justify-end gap-2">
                    <button wire:click="$set('afficherModalConfirmation', false)" class="btn-secondary">رجوع</button>
                    <button wire:click="validerCommande" class="btn-primary" wire:loading.attr="disabled">تأكيد وحفظ</button>
                </div>
            </div>
        </div>
    @endif

    {{-- ═══ Modale Nouveau Client ═══ --}}
    @if($afficherFormNouveauClient)
        <div class="modal-overlay flex items-center justify-center p-4">
            <div class="modal-panel max-w-md">
                <div class="flex items-center justify-between border-b px-4 py-2.5">
                    <h3 class="text-sm font-semibold text-gray-900">زبون جديد</h3>
                    <button type="button" wire:click="fermerModalNouveauClient" class="rounded-md p-1 text-gray-400 hover:bg-gray-100">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
                    </button>
                </div>
                <div class="px-4 py-3 space-y-2.5">
                    @if($errors->any())
                        <div class="rounded border border-red-300 bg-red-50 px-2.5 py-1.5 text-xs text-red-700">يرجى تصحيح الأخطاء.</div>
                    @endif
                    <div class="grid grid-cols-2 gap-2.5">
                        <div class="col-span-2">
                            <label class="form-label">الاسم *</label>
                            <input wire:model.live="nouveauNom" type="text" placeholder="محمد" class="form-field">
                            @error('nouveauNom') <div class="form-error">{{ $message }}</div> @enderror
                        </div>
                        <div>
                            <label class="form-label">الاسم الثاني</label>
                            <input wire:model.live="nouveauPrenom" type="text" placeholder="أحمد" class="form-field">
                        </div>
                        <div>
                            <label class="form-label">الهاتف *</label>
                            <input wire:model.live="nouveauTelephone" inputmode="numeric" maxlength="8" type="text" placeholder="22223333" class="form-field">
                            @error('nouveauTelephone') <div class="form-error">{{ $message }}</div> @enderror
                        </div>
                    </div>
                </div>
                <div class="flex justify-end gap-2 border-t px-4 py-2.5">
                    <button type="button" wire:click="fermerModalNouveauClient" class="btn-secondary">إلغاء</button>
                    <button type="button" wire:click="creerNouveauClient" wire:loading.attr="disabled" class="btn-primary">إنشاء</button>
                </div>
            </div>
        </div>
    @endif
</div>

@script
<script>
    $wire.on('imprimerTicket', ({ commandeId }) => {
        window.open(`/commandes/${commandeId}/ticket`, '_blank');
    });
</script>
@endscript
