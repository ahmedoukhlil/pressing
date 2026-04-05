<div class="page-container space-y-6 text-right" dir="rtl">

    <div class="page-header">
        <div>
            <h1 class="page-title">نظام نقاط الزبناء</h1>
            <p class="page-subtitle">ترتيب الزبناء حسب رقم الأعمال مع مقارنة شهرية.</p>
        </div>
        <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold {{ $pointsEnabled ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-600' }}">
            {{ $pointsEnabled ? 'مفعّل' : 'غير مفعّل' }}
        </span>
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

        <div class="overflow-x-auto">
            <table class="table-base w-full text-sm min-w-[650px]">
                <thead class="table-head">
                    <tr>
                        <th class="table-th !text-center border-l border-slate-200 text-[11px] w-10">#</th>
                        <th class="table-th !text-right border-l border-slate-200 text-[11px]">الزبون</th>
                        <th class="table-th !text-left border-l border-slate-200 text-[11px] whitespace-nowrap">ر.أ الإجمالي</th>
                        <th class="table-th !text-left border-l border-slate-200 text-[11px] whitespace-nowrap">{{ $labelMoisCourant }}</th>
                        <th class="table-th !text-left border-l border-slate-200 text-[11px] whitespace-nowrap">{{ $labelMoisPrev }}</th>
                        <th class="table-th !text-center text-[11px] whitespace-nowrap">التطور</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($classement as $i => $row)
                        @php
                            $rang = ($classement->currentPage() - 1) * $classement->perPage() + $loop->iteration;
                            $hasEvolution = $row['evolution'] !== null;
                        @endphp
                        <tr class="hover:bg-slate-50 transition">
                            <td class="table-td !text-center border-l border-slate-100">
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
                            <td class="table-td !text-right border-l border-slate-100">
                                <div class="font-medium text-slate-800">{{ $row['nom'] }}</div>
                                @if($row['telephone'])
                                    <div class="text-[11px] text-slate-400 num-ltr">{{ $row['telephone'] }}</div>
                                @endif
                            </td>
                            <td class="table-td !text-left border-l border-slate-100 whitespace-nowrap font-semibold text-slate-700 num-ltr tabular-nums">
                                {{ number_format($row['ca_total'], 2, ',', ' ') }}
                            </td>
                            <td class="table-td !text-left border-l border-slate-100 whitespace-nowrap num-ltr tabular-nums {{ $row['ca_mois'] > 0 ? 'text-emerald-700 font-medium' : 'text-slate-400' }}">
                                {{ $row['ca_mois'] > 0 ? number_format($row['ca_mois'], 2, ',', ' ') : '-' }}
                            </td>
                            <td class="table-td !text-left border-l border-slate-100 whitespace-nowrap num-ltr tabular-nums text-slate-500">
                                {{ $row['ca_prev'] > 0 ? number_format($row['ca_prev'], 2, ',', ' ') : '-' }}
                            </td>
                            <td class="table-td !text-center whitespace-nowrap">
                                @if(!$hasEvolution && $row['ca_mois'] > 0)
                                    <span class="inline-flex items-center gap-1 rounded-full bg-blue-50 px-2 py-0.5 text-xs text-blue-600">
                                        <i class="fi fi-rr-sparkles text-[10px]"></i> جديد
                                    </span>
                                @elseif($hasEvolution && $row['evolution'] > 0)
                                    <span class="inline-flex items-center gap-1 rounded-full bg-emerald-50 px-2 py-0.5 text-xs text-emerald-700 num-ltr">
                                        <i class="fi fi-rr-arrow-trend-up text-[10px]"></i> +{{ $row['evolution'] }}%
                                    </span>
                                @elseif($hasEvolution && $row['evolution'] < 0)
                                    <span class="inline-flex items-center gap-1 rounded-full bg-red-50 px-2 py-0.5 text-xs text-red-600 num-ltr">
                                        <i class="fi fi-rr-arrow-trend-down text-[10px]"></i> {{ $row['evolution'] }}%
                                    </span>
                                @elseif($hasEvolution)
                                    <span class="inline-flex items-center rounded-full bg-slate-100 px-2 py-0.5 text-xs text-slate-500">—</span>
                                @else
                                    <span class="text-slate-300 text-xs">—</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="table-td py-10 text-center text-slate-400">
                                <i class="fi fi-rr-folder-open text-2xl block mb-2"></i>
                                لا توجد بيانات.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($classement->hasPages())
            <div>{{ $classement->links() }}</div>
        @endif
    </div>

    {{-- Paramètres --}}
    <form wire:submit.prevent="sauvegarder" class="grid gap-4">
        <section class="card card-body space-y-4">
            <div class="flex items-start justify-between gap-3">
                <div>
                    <h2 class="card-title !mb-1">إعدادات الولاء</h2>
                    <p class="text-xs text-slate-500">يمكن تغيير القيم في أي وقت حسب سياسة المغسلة.</p>
                </div>
                <label class="inline-flex items-center gap-2 rounded-lg border border-blue-200 bg-blue-50 px-2.5 py-1.5 text-xs font-medium text-blue-800">
                    <input type="checkbox" wire:model.live="pointsEnabled" class="rounded border-blue-300 text-blue-600 focus:ring-blue-500">
                    <span>تفعيل البرنامج</span>
                </label>
            </div>

            <div class="grid gap-3 md:grid-cols-2">
                <div>
                    <label class="form-label">قاعدة الكسب: 1 نقطة لكل</label>
                    <div class="relative">
                        <input type="number" step="0.01" min="0.01" wire:model.live="pointsMruPerPoint" class="form-field ps-14 num-ltr text-left">
                        <span class="absolute inset-y-0 start-3 inline-flex items-center text-xs text-slate-500 num-ltr">MRU</span>
                    </div>
                    <p class="mt-1 text-[11px] text-slate-500">كلما انخفضت القيمة، النقاط تزيد أسرع.</p>
                    @error('pointsMruPerPoint') <div class="form-error">{{ $message }}</div> @enderror
                </div>
                <div>
                    <label class="form-label">قاعدة التحويل: 1 نقطة =</label>
                    <div class="relative">
                        <input type="number" step="0.01" min="0.01" wire:model.live="pointsMruDiscountPerPoint" class="form-field ps-14 num-ltr text-left">
                        <span class="absolute inset-y-0 start-3 inline-flex items-center text-xs text-slate-500 num-ltr">MRU</span>
                    </div>
                    <p class="mt-1 text-[11px] text-slate-500">تحدد قيمة الخصم عند استعمال النقاط.</p>
                    @error('pointsMruDiscountPerPoint') <div class="form-error">{{ $message }}</div> @enderror
                </div>
            </div>
        </section>

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
