<div class="page-container space-y-6">
    <div class="page-header">
        <div>
            <h1 class="page-title">إيداع الملابس</h1>
            <p class="page-subtitle">اختيار الزبون، إضافة الخدمات، والدفع الاختياري عند إيداع الملابس.</p>
        </div>
    </div>

    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="grid lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 space-y-4">
            <div class="card card-body space-y-3">
                <div class="font-medium">الزبون</div>
                <div class="flex gap-2">
                    <input type="tel" inputmode="numeric" maxlength="8" wire:model.live.debounce.300ms="rechercheClient" placeholder="هاتف الزبون (8 أرقام)" class="form-field">
                </div>

                @if($clientInfo)
                    <div class="rounded bg-green-50 border border-green-200 p-2 text-sm">
                        <div><strong>{{ $clientInfo['nom'] }}</strong></div>
                        <div>{{ $clientInfo['telephone'] }}</div>
                        <button wire:click="resetClient" class="text-xs text-red-600 mt-1">إزالة الزبون</button>
                    </div>
                @endif

                @if(!empty($clientsTrouves))
                    <div class="space-y-2">
                        <div class="text-sm text-gray-700">اختر زبونًا:</div>
                        @foreach($clientsTrouves as $c)
                            <button wire:click="selectionnerClient({{ $c['id'] }})" class="w-full text-left rounded border px-3 py-2 text-sm hover:bg-gray-50">
                                {{ $c['nom'] }} - {{ $c['telephone'] }}
                            </button>
                        @endforeach
                    </div>
                @endif

            </div>

            <div class="card card-body">
                <div class="font-medium mb-3">الخدمات</div>
                <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-3">
                    @forelse($services as $service)
                        <button wire:click="ajouterAuPanier({{ $service->id }})" class="rounded-lg border border-slate-200 p-3 bg-white text-left hover:bg-slate-50">
                            <div class="text-lg">{{ $service->icone ?: '🧺' }} {{ $service->libelle_ar ?: '-' }}</div>
                            <div class="text-sm text-gray-600"><span class="num-ltr">{{ number_format((float) $service->prix, 2, ',', ' ') }} MRU</span></div>
                        </button>
                    @empty
                        <div class="text-sm text-gray-500">لا توجد خدمات مهيأة.</div>
                    @endforelse
                </div>
            </div>
        </div>

        <div class="card card-body space-y-3">
            <div class="font-medium">السلة</div>
            @forelse($panier as $i => $item)
                <div class="border rounded p-2" wire:key="panier-item-{{ $i }}-qte{{ $item['quantite'] }}">
                    <div class="text-sm font-medium">{{ $item['libelle'] }}</div>
                    <div class="flex items-center gap-2 mt-1">
                        <span class="text-sm num-ltr">{{ number_format((float) $item['sous_total'], 2, ',', ' ') }} MRU</span>
                        <button wire:click="retirerDuPanier({{ $i }})" class="text-red-600 text-xs">حذف</button>
                    </div>
                    <div class="mt-1.5 flex items-center gap-1.5">
                        <div class="inline-flex items-center shrink-0 rounded-md border border-slate-300 bg-slate-50 h-7"
                             x-data="{ qte: {{ (int) $item['quantite'] }} }">
                            <button type="button"
                                    @click="qte = Math.max(1, qte - 1); $wire.modifierQuantite({{ $i }}, qte)"
                                    class="px-1.5 text-slate-500 hover:text-slate-900 text-sm leading-none select-none">−</button>
                            <span class="w-6 text-center text-xs font-semibold text-slate-800" x-text="qte"></span>
                            <button type="button"
                                    @click="qte++; $wire.modifierQuantite({{ $i }}, qte)"
                                    class="px-1.5 text-slate-500 hover:text-slate-900 text-sm leading-none select-none">+</button>
                        </div>
                        <input
                            type="text"
                            value="{{ $item['notes'] }}"
                            @change="$wire.modifierObservation({{ $i }}, $event.target.value)"
                            class="flex-1 h-7 rounded-md border-slate-300 text-xs px-2"
                            placeholder="ملاحظات ..."
                        >
                    </div>
                </div>
            @empty
                <div class="text-sm text-gray-500">لا توجد عناصر.</div>
            @endforelse

            <div class="border-t pt-2">
                <label class="text-sm">نسبة الخصم (%)</label>
                <input type="number" min="0" max="100" step="0.01" wire:model.live="remisePourcentage" class="form-field">
                @error('remisePourcentage') <div class="form-error">{{ $message }}</div> @enderror
            </div>

            <div class="border-t pt-2 text-sm">
                <div class="flex justify-between"><span>الإجمالي قبل الخصم</span><strong class="num-ltr">{{ number_format((float) $this->montant_total, 2, ',', ' ') }} MRU</strong></div>
                <div class="flex justify-between"><span>الخصم</span><span class="num-ltr">{{ number_format((float) $this->remise_montant, 2, ',', ' ') }} MRU ({{ number_format((float) $remisePourcentage, 2, ',', ' ') }}%)</span></div>
                <div class="flex justify-between"><span>الإجمالي بعد الخصم</span><strong class="num-ltr">{{ number_format((float) $this->montant_total_net, 2, ',', ' ') }} MRU</strong></div>
                <div class="flex justify-between"><span>المدفوع</span><span class="num-ltr">{{ number_format((float) $montantPaye, 2, ',', ' ') }} MRU</span></div>
                <div class="flex justify-between"><span>المتبقي</span><span class="num-ltr">{{ number_format((float) $this->reste_a_payer, 2, ',', ' ') }} MRU</span></div>
            </div>

            <button wire:click="ouvrirModalPaiement" class="w-full btn-primary" wire:loading.attr="disabled">تأكيد الطلب</button>
            @error('client') <div class="form-error">{{ $message }}</div> @enderror
            @error('panier') <div class="form-error">{{ $message }}</div> @enderror
        </div>
    </div>

    @if($afficherModalPaiement)
        <div class="modal-overlay flex items-center justify-center p-4">
            <div class="modal-panel max-w-md p-4 space-y-3">
                <div class="text-lg font-medium">الدفع</div>
                <div class="alert alert-info text-xs">
                    الدفع عند إيداع الملابس اختياري. يمكنك تركه 0 (غير مدفوع) أو إدخال دفعة مقدمة.
                </div>
                <div>
                    <label class="text-sm">طريقة الدفع</label>
                    <select wire:model="modeReglement" class="form-field">
                        <option value="especes">نقدًا</option>
                        <option value="carte">بطاقة</option>
                        <option value="virement">تحويل</option>
                        <option value="non_paye" @disabled($montantPaye > 0)>غير مدفوع</option>
                    </select>
                    @error('modeReglement') <div class="form-error">{{ $message }}</div> @enderror
                </div>
                <div>
                    <label class="text-sm">المبلغ المدفوع (دفعة مقدمة)</label>
                    <input type="number" step="0.01" min="0" wire:model="montantPaye" class="form-field">
                    <div class="mt-1 text-xs text-gray-500">
                        إجمالي قبل الخصم: <strong class="num-ltr">{{ number_format((float) $this->montant_total, 2, ',', ' ') }} MRU</strong> -
                        الخصم: <strong class="num-ltr">{{ number_format((float) $this->remise_montant, 2, ',', ' ') }} MRU</strong> -
                        الإجمالي بعد الخصم: <strong class="num-ltr">{{ number_format((float) $this->montant_total_net, 2, ',', ' ') }} MRU</strong> -
                        المتبقي بعد الإيداع: <strong class="num-ltr">{{ number_format((float) $this->reste_a_payer, 2, ',', ' ') }} MRU</strong>
                    </div>
                    @error('montantPaye') <div class="form-error">{{ $message }}</div> @enderror
                </div>
                <div>
                    <label class="text-sm">ملاحظات</label>
                    <textarea wire:model="notes" class="form-field" rows="2"></textarea>
                </div>
                <div class="flex justify-end gap-2">
                    <button wire:click="$set('afficherModalPaiement', false)" class="btn-secondary">إلغاء</button>
                    <button wire:click="validerCommande" class="btn-primary" wire:loading.attr="disabled">تأكيد</button>
                </div>
            </div>
        </div>
    @endif

    @if($afficherFormNouveauClient)
        <div class="modal-overlay">
            <div class="min-h-full flex items-center justify-center p-4 sm:p-6">
                <div class="modal-panel max-w-xl">
                    <div class="flex items-center justify-between border-b px-4 py-3 sm:px-5">
                        <div>
                            <h3 class="text-base sm:text-lg font-semibold text-gray-900">زبون جديد</h3>
                            <p class="text-xs sm:text-sm text-gray-600">لم يتم العثور على زبون. أنشئ الزبون دون مغادرة الصفحة.</p>
                        </div>
                        <button type="button" wire:click="fermerModalNouveauClient" class="rounded-md p-1.5 text-gray-500 hover:bg-gray-100 hover:text-gray-700">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>

                    <div class="px-4 py-4 sm:px-5 space-y-3">
                        @if($errors->any())
                            <div class="rounded-lg border border-red-300 bg-red-50 px-3 py-2 text-sm text-red-700">
                                يرجى تصحيح الحقول التي تحتوي على أخطاء.
                            </div>
                        @endif

                        <div class="grid sm:grid-cols-2 gap-3">
                            <div class="sm:col-span-2">
                                <label class="mb-1 block text-sm font-medium text-gray-700">الاسم *</label>
                                <input wire:model.live="nouveauNom" type="text" placeholder="Ex: Mohamed" class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500">
                                @error('nouveauNom') <div class="text-xs text-red-600 mt-1">{{ $message }}</div> @enderror
                            </div>
                            <div>
                                <label class="mb-1 block text-sm font-medium text-gray-700">الاسم الثاني</label>
                                <input wire:model.live="nouveauPrenom" type="text" placeholder="Ex: Ahmed" class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500">
                            </div>
                            <div>
                                <label class="mb-1 block text-sm font-medium text-gray-700">الهاتف *</label>
                                <input wire:model.live="nouveauTelephone" inputmode="numeric" maxlength="8" type="text" placeholder="مثال: 22223333" class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500">
                                @error('nouveauTelephone') <div class="text-xs text-red-600 mt-1">{{ $message }}</div> @enderror
                            </div>
                        </div>
                    </div>

                    <div class="flex justify-end gap-2 border-t px-4 py-3 sm:px-5">
                        <button type="button" wire:click="fermerModalNouveauClient" class="px-3 py-2 rounded-lg border text-sm">إلغاء</button>
                        <button type="button" wire:click="creerNouveauClient" wire:loading.attr="disabled" class="px-3 py-2 rounded-lg bg-blue-700 text-white text-sm hover:bg-blue-800">إنشاء زبون</button>
                    </div>
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
