<div class="page-container">
    <div class="page-header">
        <div>
            <h1 class="page-title">طرق الدفع</h1>
            <p class="page-subtitle">تهيئة طرق الدفع المستخدمة في التحصيل والمصروفات.</p>
        </div>
        <button wire:click="nouveauMode" class="btn-primary">جديد</button>
    </div>

    @if($afficherForm)
        <form wire:submit.prevent="sauvegarder" class="card card-body mb-4 grid md:grid-cols-5 gap-3">
            <div>
                <label class="form-label">الاسم *</label>
                <input wire:model.live="libelle" type="text" placeholder="اسم الطريقة" class="form-field">
                @error('libelle') <div class="form-error">{{ $message }}</div> @enderror
            </div>
            <div>
                <label class="form-label">الرمز *</label>
                <input wire:model.live="code" type="text" placeholder="الرمز" class="form-field">
                @error('code') <div class="form-error">{{ $message }}</div> @enderror
            </div>
            <div>
                <label class="form-label">الأيقونة</label>
                <input wire:model.live="icone" type="text" placeholder="الأيقونة" class="form-field">
            </div>
            <div>
                <label class="form-label">الترتيب</label>
                <input wire:model.live="ordre" type="number" min="0" placeholder="الترتيب" class="form-field">
                @error('ordre') <div class="form-error">{{ $message }}</div> @enderror
            </div>
            <div class="flex items-end">
                <label class="inline-flex items-center gap-2 text-sm"><input wire:model.live="actif" type="checkbox" class="rounded border-slate-300"> نشط</label>
            </div>
            <div class="md:col-span-5 flex gap-2">
                <button type="submit" class="btn-primary" wire:loading.attr="disabled">حفظ</button>
                <button type="button" wire:click="$set('afficherForm', false)" class="btn-secondary">إلغاء</button>
            </div>
        </form>
    @endif

    <div class="table-wrap">
        <table class="table-base">
            <thead class="table-head">
            <tr>
                <th class="table-th">الاسم</th>
                <th class="table-th">الرمز</th>
                <th class="table-th">الترتيب</th>
                <th class="table-th">الحالة</th>
                <th class="table-th text-right">الإجراءات</th>
            </tr>
            </thead>
            <tbody>
            @forelse($modes as $mode)
                <tr class="table-row">
                    <td class="table-td">{{ $mode->icone }} {{ $mode->libelle }}</td>
                    <td class="table-td">{{ $mode->code }}</td>
                    <td class="table-td">{{ $mode->ordre }}</td>
                    <td class="table-td">
                        <span class="status-badge {{ $mode->actif ? 'status-success' : 'status-neutral' }}">
                            {{ $mode->actif ? 'نشط' : 'غير نشط' }}
                        </span>
                    </td>
                    <td class="table-td text-right">
                        <button wire:click="editer({{ $mode->id }})" class="text-blue-700 text-xs">تعديل</button>
                        <button wire:click="toggleActif({{ $mode->id }})" onclick="return confirm('تأكيد تغيير الحالة؟')" class="text-orange-700 text-xs ml-2">{{ $mode->actif ? 'تعطيل' : 'تفعيل' }}</button>
                        @if(!$mode->est_systeme)
                            <button wire:click="supprimer({{ $mode->id }})" onclick="return confirm('حذف طريقة الدفع هذه؟')" class="text-red-700 text-xs ml-2">حذف</button>
                        @else
                            <span class="text-xs text-gray-400 ml-2">نظام</span>
                        @endif
                    </td>
                </tr>
            @empty
                <tr><td colspan="5" class="table-td text-center text-gray-500">لا توجد طرق دفع.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>
