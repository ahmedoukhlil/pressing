<div class="page-container space-y-5">
    <div class="page-header">
        <div>
            <h1 class="page-title">إدارة مخزون المواد الاستهلاكية</h1>
            <p class="page-subtitle">تتبع دخول وخروج مواد مثل الصابون وماء الجافيل.</p>
        </div>
        <a href="{{ route('exports.stock.pdf') }}" class="btn-secondary">تصدير PDF</a>
    </div>

    <div class="grid lg:grid-cols-3 gap-4">
        <div class="card card-body lg:col-span-1">
            <h2 class="card-title">إضافة مادة استهلاكية</h2>
            <div class="space-y-3">
                <div>
                    <label class="form-label">الاسم</label>
                    <input wire:model.live="libelle" type="text" class="form-field" placeholder="مثال: صابون">
                    @error('libelle') <div class="form-error">{{ $message }}</div> @enderror
                </div>
                <div>
                    <label class="form-label">الوحدة</label>
                    <input wire:model.live="unite" type="text" class="form-field" placeholder="لتر / كغ / قطعة">
                    @error('unite') <div class="form-error">{{ $message }}</div> @enderror
                </div>
                <div>
                    <label class="form-label">حد التنبيه</label>
                    <input wire:model.live="seuilAlerte" type="number" step="0.01" min="0" class="form-field">
                    @error('seuilAlerte') <div class="form-error">{{ $message }}</div> @enderror
                </div>
                <button wire:click="ajouterConsommable" class="btn-primary w-full">إضافة</button>
            </div>
        </div>

        <div class="card card-body lg:col-span-2">
            <div class="flex flex-wrap items-center justify-between gap-2 mb-3">
                <h2 class="card-title mb-0">المخزون الحالي</h2>
                <input wire:model.live.debounce.400ms="recherche" type="text" class="form-field md:max-w-xs" placeholder="بحث...">
            </div>

            <div class="table-wrap">
                <table class="table-base">
                    <thead class="table-head">
                        <tr>
                            <th class="table-th">المادة</th>
                            <th class="table-th">الوحدة</th>
                            <th class="table-th">المخزون</th>
                            <th class="table-th">الحالة</th>
                            <th class="table-th text-right">الحركة</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($consommables as $item)
                            @php
                                $stock = (float) $item->stock_actuel;
                                $seuil = (float) $item->seuil_alerte;
                                $alerte = $stock <= $seuil;
                            @endphp
                            <tr class="table-row">
                                <td class="table-td">{{ $item->libelle }}</td>
                                <td class="table-td">{{ $item->unite }}</td>
                                <td class="table-td"><span class="num-ltr">{{ number_format($stock, 2, ',', ' ') }}</span></td>
                                <td class="table-td">
                                    <span class="status-badge {{ $alerte ? 'status-warning' : 'status-success' }}">
                                        {{ $alerte ? 'تنبيه مخزون' : 'جيد' }}
                                    </span>
                                </td>
                                <td class="table-td text-right">
                                    <div class="inline-flex gap-2">
                                        <button wire:click="ouvrirMouvement({{ $item->id }}, 'entree')" class="btn-secondary">دخول</button>
                                        <button wire:click="ouvrirMouvement({{ $item->id }}, 'sortie')" class="btn-secondary">خروج</button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="table-td text-center text-slate-500">لا توجد مواد مضافة.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="mt-3">{{ $consommables->links() }}</div>
        </div>
    </div>

    <div class="grid lg:grid-cols-2 gap-4">
        <div class="card card-body">
            <h2 class="card-title">تسجيل حركة</h2>

            @if($consommableSelectionne)
                <div class="mb-3 text-sm text-slate-600">
                    المادة: <span class="font-semibold text-slate-900">{{ $consommableSelectionne->libelle }}</span> |
                    المخزون الحالي: <span class="num-ltr font-semibold text-slate-900">{{ number_format((float) $consommableSelectionne->stock_actuel, 2, ',', ' ') }}</span> {{ $consommableSelectionne->unite }}
                </div>

                <div class="space-y-3">
                    <div>
                        <label class="form-label">نوع الحركة</label>
                        <select wire:model.live="typeMouvement" class="form-field">
                            <option value="entree">دخول</option>
                            <option value="sortie">خروج</option>
                        </select>
                        @error('typeMouvement') <div class="form-error">{{ $message }}</div> @enderror
                    </div>
                    <div>
                        <label class="form-label">الكمية</label>
                        <input wire:model.live="quantite" type="number" step="0.01" min="0.01" class="form-field">
                        @error('quantite') <div class="form-error">{{ $message }}</div> @enderror
                    </div>
                    <div>
                        <label class="form-label">السبب {{ $typeMouvement === 'sortie' ? '(إجباري)' : '(اختياري)' }}</label>
                        <input wire:model.live="motif" type="text" class="form-field" placeholder="شراء / استعمال يومي / تلف ...">
                        @error('motif') <div class="form-error">{{ $message }}</div> @enderror
                    </div>
                    <div>
                        <label class="form-label">ملاحظة</label>
                        <textarea wire:model.live="notes" class="form-field" rows="2"></textarea>
                        @error('notes') <div class="form-error">{{ $message }}</div> @enderror
                    </div>
                    <button wire:click="enregistrerMouvement" class="btn-primary w-full">حفظ الحركة</button>
                </div>
            @else
                <div class="text-sm text-slate-500">اختر مادة ثم اضغط دخول أو خروج لتسجيل حركة.</div>
            @endif
        </div>

        <div class="card card-body">
            <h2 class="card-title">آخر الحركات</h2>
            <div class="space-y-2">
                @forelse($mouvements as $m)
                    <div class="rounded-lg border border-slate-200 px-3 py-2">
                        <div class="flex items-center justify-between text-sm">
                            <div class="font-medium text-slate-900">{{ $m->consommable?->libelle ?? '-' }}</div>
                            <span class="status-badge {{ $m->type_mouvement === 'entree' ? 'status-success' : 'status-warning' }}">
                                {{ $m->type_mouvement === 'entree' ? 'دخول' : 'خروج' }}
                            </span>
                        </div>
                        <div class="mt-1 text-xs text-slate-600">
                            كمية: <span class="num-ltr">{{ number_format((float) $m->quantite, 2, ',', ' ') }}</span> |
                            تاريخ: {{ $m->date_mouvement?->format('d/m/Y H:i') }}
                        </div>
                        @if($m->motif)
                            <div class="text-xs text-slate-500 mt-1">السبب: {{ $m->motif }}</div>
                        @endif
                    </div>
                @empty
                    <div class="text-sm text-slate-500">لا توجد حركات بعد.</div>
                @endforelse
            </div>
        </div>
    </div>
</div>
