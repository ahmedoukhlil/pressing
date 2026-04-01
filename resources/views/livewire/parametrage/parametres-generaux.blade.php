<div class="page-container space-y-4 text-right" dir="rtl">
    <div class="page-header">
        <div>
            <h1 class="page-title">نظام نقاط الزبناء</h1>
            <p class="page-subtitle">تحكم في طريقة احتساب النقاط وتحويلها إلى خصم بشكل واضح وسريع.</p>
        </div>
        <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold {{ $pointsEnabled ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-600' }}">
            {{ $pointsEnabled ? 'مفعّل' : 'غير مفعّل' }}
        </span>
    </div>

    @if (session('success'))
        <div class="rounded-lg border border-green-300 bg-green-50 px-3 py-2 text-sm text-green-800">{{ session('success') }}</div>
    @endif

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
