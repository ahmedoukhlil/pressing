<div class="page-container">
    <div class="page-header">
        <div>
            <h1 class="page-title">حساب الزبون</h1>
            <p class="page-subtitle"><span class="num-ltr">{{ $client->code_client ?? '-' }}</span> | {{ $client->full_name }} | {{ $client->telephone }}</p>
        </div>
        <a href="{{ route('clients.index') }}" wire:navigate class="btn-secondary">الرجوع إلى الزبناء</a>
    </div>

    <div class="card card-body mb-5">
        <div class="grid gap-3 text-sm md:grid-cols-4">
            <div>
                <p class="text-slate-500">إجمالي الطلبات</p>
                <p class="font-semibold text-slate-900">{{ number_format($totalFacture, 2) }} MRU</p>
            </div>
            <div>
                <p class="text-slate-500">إجمالي المدفوع</p>
                <p class="font-semibold text-emerald-700">{{ number_format($totalPaye, 2) }} MRU</p>
            </div>
            <div>
                <p class="text-slate-500">على الزبون</p>
                <p class="font-semibold {{ $clientDoit > 0 ? 'text-amber-700' : 'text-slate-700' }}">{{ number_format($clientDoit, 2) }} MRU</p>
            </div>
            <div>
                <p class="text-slate-500">على المغسلة</p>
                <p class="font-semibold {{ $pressingDoit > 0 ? 'text-rose-700' : 'text-slate-700' }}">{{ number_format($pressingDoit, 2) }} MRU</p>
            </div>
        </div>
    </div>

    @if(($loyaltySettings['enabled'] ?? false))
        <div class="card card-body mb-5">
            <h2 class="card-title">برنامج نقاط الولاء</h2>
            <div class="grid gap-3 text-sm md:grid-cols-4">
                <div>
                    <p class="text-slate-500">الرصيد الحالي</p>
                    <p class="font-semibold text-amber-700 num-ltr">{{ number_format((int) ($wallet?->solde_points ?? 0)) }} نقطة</p>
                </div>
                <div>
                    <p class="text-slate-500">إجمالي النقاط المكتسبة</p>
                    <p class="font-semibold text-emerald-700 num-ltr">{{ number_format((int) ($wallet?->total_points_gagnes ?? 0)) }} نقطة</p>
                </div>
                <div>
                    <p class="text-slate-500">إجمالي النقاط المستخدمة</p>
                    <p class="font-semibold text-purple-700 num-ltr">{{ number_format((int) ($wallet?->total_points_utilises ?? 0)) }} نقطة</p>
                </div>
                <div>
                    <p class="text-slate-500">قيمة النقطة للخصم</p>
                    <p class="font-semibold text-slate-900 num-ltr">1 = {{ number_format((float) ($loyaltySettings['mru_discount_per_point'] ?? 0), 2) }} MRU</p>
                </div>
            </div>

            <div class="mt-4">
                <h3 class="text-sm font-semibold text-slate-800 mb-2">آخر حركات النقاط</h3>
                @forelse($pointTransactions as $tx)
                    <div class="flex items-center justify-between gap-2 rounded-lg border border-slate-200 px-3 py-2 mb-2 last:mb-0">
                        <div>
                            <p class="text-sm font-medium text-slate-800">
                                @if($tx->type === 'gain')
                                    كسب نقاط
                                @elseif($tx->type === 'utilisation')
                                    استخدام نقاط
                                @else
                                    حركة نقاط
                                @endif
                                @if($tx->commande?->numero_commande)
                                    <span class="text-slate-500">- {{ $tx->commande->numero_commande }}</span>
                                @endif
                            </p>
                            <p class="text-xs text-slate-500">{{ optional($tx->created_at)->format('d/m/Y H:i') }}</p>
                        </div>
                        <div class="text-right">
                            <p class="font-semibold num-ltr {{ $tx->type === 'gain' ? 'text-emerald-700' : 'text-purple-700' }}">
                                {{ $tx->type === 'gain' ? '+' : '-' }}{{ number_format((int) $tx->points) }} نقطة
                            </p>
                            <p class="text-xs text-slate-500 num-ltr">{{ number_format((float) $tx->valeur_mru, 2) }} MRU</p>
                        </div>
                    </div>
                @empty
                    <div class="empty-state">لا توجد حركات نقاط بعد.</div>
                @endforelse
                <div class="mt-3">{{ $pointTransactions->links() }}</div>
            </div>
        </div>
    @endif

    <div class="card card-body">
        <h2 class="card-title">لائحة الطلبات</h2>

        @forelse($commandes as $commande)
            <div class="rounded-lg border border-slate-200 p-3 mb-3 last:mb-0" wire:key="commande-{{ $commande->id }}">
                <div class="flex flex-wrap items-center justify-between gap-2 mb-2">
                    <div class="text-sm text-slate-700">
                        <span class="font-semibold">الطلبية رقم: {{ $commande->numero_commande }}</span>
                        <span class="text-slate-500">- تاريخ الإيداع: {{ optional($commande->date_depot)->format('d/m/Y H:i') }}</span>
                    </div>
                    <span class="status-badge {{ (float) $commande->reste_a_payer > 0 ? 'status-warning' : 'status-success' }}">
                        {{ (float) $commande->reste_a_payer > 0 ? 'متبقي للدفع' : 'مدفوع بالكامل' }}
                    </span>
                </div>

                <div class="grid gap-2 text-sm md:grid-cols-4 mb-2">
                    <div>
                        <p class="text-slate-500">الإجمالي</p>
                        <p class="font-medium text-slate-900">{{ number_format((float) $commande->montant_total, 2) }} MRU</p>
                    </div>
                    <div>
                        <p class="text-slate-500">المدفوع</p>
                        <p class="font-medium text-emerald-700">{{ number_format((float) $commande->montant_paye, 2) }} MRU</p>
                    </div>
                    <div>
                        <p class="text-slate-500">المتبقي</p>
                        <p class="font-medium {{ (float) $commande->reste_a_payer > 0 ? 'text-amber-700' : 'text-slate-700' }}">
                            {{ number_format((float) $commande->reste_a_payer, 2) }} MRU
                        </p>
                    </div>
                    <div>
                        <p class="text-slate-500">الحالة</p>
                        <p class="font-medium text-slate-900">{{ $commande->statut_label }}</p>
                    </div>
                </div>

                <div class="rounded-md bg-slate-50 p-2">
                    <p class="mb-1 text-xs font-medium text-slate-600">الخدمات المفوترة</p>
                    @forelse($commande->details as $detail)
                        <div class="flex items-center justify-between gap-2 text-sm py-1 border-b border-slate-200 last:border-b-0">
                            <div class="text-slate-700">
                                {{ $detail->service?->libelle_ar ?: '-' }}
                                <span class="text-slate-500">x{{ $detail->quantite }}</span>
                            </div>
                            <div class="font-medium text-slate-800">{{ number_format((float) $detail->sous_total, 2) }} MRU</div>
                        </div>
                    @empty
                        <p class="text-sm text-slate-500">لا توجد تفاصيل لهذه الطلبية.</p>
                    @endforelse
                </div>

                @if($commande->caisseOperations->isNotEmpty())
                    <div class="rounded-md bg-emerald-50 p-2 mt-2">
                        <p class="mb-1 text-xs font-medium text-emerald-700">المدفوعات</p>
                        @foreach($commande->caisseOperations as $op)
                            <div class="flex items-center justify-between gap-2 text-sm py-1 border-b border-emerald-100 last:border-b-0">
                                <div class="text-slate-600">
                                    <span>{{ $modesPaiement[$op->mode_paiement] ?? $op->mode_paiement ?? '-' }}</span>
                                    <span class="text-slate-400 text-xs num-ltr ml-1">{{ optional($op->date_operation)->format('d/m/Y H:i') }}</span>
                                </div>
                                <div class="font-medium text-emerald-700 num-ltr">{{ number_format((float) $op->montant_operation, 2) }} MRU</div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        @empty
            <div class="empty-state">لا توجد طلبات لهذا الزبون.</div>
        @endforelse

        <div class="mt-3">{{ $commandes->links() }}</div>
    </div>
</div>
