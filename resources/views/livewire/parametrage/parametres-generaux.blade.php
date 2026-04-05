<div class="page-container space-y-6 text-right" dir="rtl">

    <div class="page-header">
        <div>
            <h1 class="page-title">ترتيب الزبناء</h1>
            <p class="page-subtitle">ترتيب الزبناء حسب رقم الأعمال مع مقارنة شهرية.</p>
        </div>
    </div>

    @if (session('success'))
        <div class="rounded-lg border border-green-300 bg-green-50 px-3 py-2 text-sm text-green-800">{{ session('success') }}</div>
    @endif

    {{-- Classement clients --}}
    <div class="card card-body space-y-4">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <h2 class="card-title mb-0">ترتيب الزبناء حسب رقم الأعمال</h2>
                <p class="text-xs text-slate-400 mt-0.5">مرتب من الأعلى إلى الأدنى · المقارنة مع الشهر السابق</p>
            </div>
            <div class="flex flex-wrap items-center gap-2">
                <select wire:model.live="moisCA" class="form-field w-auto min-w-[130px]">
                    @foreach($moisLabels as $num => $label)
                        <option value="{{ $num }}">{{ $label }}</option>
                    @endforeach
                </select>
                <select wire:model.live="anneeCA" class="form-field w-auto min-w-[90px]">
                    @foreach(range(now()->year, now()->year - 5) as $y)
                        <option value="{{ $y }}">{{ $y }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="table-wrap">
            <table class="table-base">
                <thead class="table-head">
                    <tr>
                        <th class="table-th text-center">#</th>
                        <th class="table-th">الزبون</th>
                        <th class="table-th">{{ $labelMoisCourant }}</th>
                        <th class="table-th">{{ $labelMoisPrev }}</th>
                        <th class="table-th text-center">التطور</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($classement as $row)
                        @php
                            $rang = ($classement->currentPage() - 1) * $classement->perPage() + $loop->iteration;
                            $hasEvolution = $row['evolution'] !== null;
                        @endphp
                        <tr class="table-row">
                            <td class="table-td text-center">
                                @if($rang === 1)
                                    <span class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-yellow-400 text-white text-xs font-bold">1</span>
                                @elseif($rang === 2)
                                    <span class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-slate-300 text-white text-xs font-bold">2</span>
                                @elseif($rang === 3)
                                    <span class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-amber-600 text-white text-xs font-bold">3</span>
                                @else
                                    <span class="text-xs text-slate-400 num-ltr">{{ $rang }}</span>
                                @endif
                            </td>
                            <td class="table-td">
                                <div class="font-medium text-slate-800">{{ $row['nom'] }}</div>
                                @if($row['telephone'])
                                    <div class="text-[11px] text-slate-400 num-ltr">{{ $row['telephone'] }}</div>
                                @endif
                            </td>
                            <td class="table-td font-semibold text-emerald-700 num-ltr tabular-nums">
                                {{ number_format($row['ca_mois'], 2, ',', ' ') }} MRU
                            </td>
                            <td class="table-td num-ltr tabular-nums text-slate-500">
                                {{ $row['ca_prev'] > 0 ? number_format($row['ca_prev'], 2, ',', ' ') . ' MRU' : '-' }}
                            </td>
                            <td class="table-td text-center">
                                @if($row['evolution'] === null)
                                    <span class="status-badge bg-blue-50 text-blue-600"><i class="fi fi-rr-sparkles"></i> جديد</span>
                                @elseif($row['evolution'] > 0)
                                    <span class="status-badge status-success num-ltr"><i class="fi fi-rr-arrow-trend-up"></i> +{{ $row['evolution'] }}%</span>
                                @elseif($row['evolution'] < 0)
                                    <span class="status-badge status-danger num-ltr"><i class="fi fi-rr-arrow-trend-down"></i> {{ $row['evolution'] }}%</span>
                                @else
                                    <span class="status-badge">—</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="table-td text-center text-gray-500">لا توجد بيانات.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

    </div>

    {{-- Paramètres --}}
    <form wire:submit.prevent="sauvegarder" class="grid gap-4">
        <section class="card card-body space-y-3">
            <h2 class="card-title !mb-0">معلومات الطباعة</h2>
            <div class="grid gap-3 md:grid-cols-2">
                <div>
                    <label class="form-label">اسم المغسلة *</label>
                    <input type="text" wire:model.live="nomPressing" class="form-field" placeholder="مغاسل ...">
                    @error('nomPressing') <div class="form-error">{{ $message }}</div> @enderror
                </div>
                <div>
                    <label class="form-label">الهاتف</label>
                    <input type="text" wire:model.live="telephone" class="form-field num-ltr text-left" placeholder="32770404">
                    @error('telephone') <div class="form-error">{{ $message }}</div> @enderror
                </div>
                <div class="md:col-span-2">
                    <label class="form-label">العنوان</label>
                    <input type="text" wire:model.live="adresse" class="form-field" placeholder="تيارت - نواكشوط">
                    @error('adresse') <div class="form-error">{{ $message }}</div> @enderror
                </div>
                <div class="md:col-span-2">
                    <label class="form-label">نص أسفل التذكرة</label>
                    <input type="text" wire:model.live="footerTicket" class="form-field" placeholder="شكرا لثقتكم">
                    @error('footerTicket') <div class="form-error">{{ $message }}</div> @enderror
                </div>
            </div>
        </section>

        <div class="flex items-center justify-end gap-2">
            <button type="submit" class="btn-primary" wire:loading.attr="disabled">
                حفظ التعديلات
            </button>
        </div>
    </form>
</div>
