<div class="page-container">
    <div class="page-header">
        <div>
            <h1 class="page-title">أنواع المصروفات</h1>
            <p class="page-subtitle">تصنيفات تستخدم لتنظيم المصروفات.</p>
        </div>
        <button wire:click="nouveauType" class="btn-primary">جديد</button>
    </div>

    @if($afficherForm)
        <form wire:submit.prevent="sauvegarder" class="card card-body mb-4 grid md:grid-cols-5 gap-3">
            <div>
                <label class="form-label">الاسم *</label>
                <input wire:model.live="libelle" type="text" placeholder="اسم النوع" class="form-field">
                @error('libelle') <div class="form-error">{{ $message }}</div> @enderror
            </div>
            <div>
                <label class="form-label">الأيقونة</label>
                <input wire:model.live="icone" type="text" placeholder="الأيقونة" class="form-field">
            </div>
            <div>
                <label class="form-label">اللون *</label>
                <input wire:model.live="couleur" type="text" placeholder="#6B7280" class="form-field">
                @error('couleur') <div class="form-error">{{ $message }}</div> @enderror
            </div>
            <div>
                <label class="form-label">الترتيب</label>
                <input wire:model.live="ordre" type="number" min="0" class="form-field">
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
                    <th class="table-th">النوع</th>
                    <th class="table-th">اللون</th>
                    <th class="table-th">الترتيب</th>
                    <th class="table-th">الحالة</th>
                    <th class="table-th text-right">الإجراءات</th>
                </tr>
            </thead>
            <tbody>
                @forelse($types as $type)
                    <tr class="table-row">
                        <td class="table-td">{{ $type->icone }} {{ $type->libelle }}</td>
                        <td class="table-td">
                            <span class="inline-flex items-center gap-2">
                                <span class="inline-block h-3 w-3 rounded-full" style="background-color: {{ $type->couleur }}"></span>
                                {{ $type->couleur }}
                            </span>
                        </td>
                        <td class="table-td">{{ $type->ordre }}</td>
                        <td class="table-td"><span class="status-badge {{ $type->actif ? 'status-success' : 'status-neutral' }}">{{ $type->actif ? 'نشط' : 'غير نشط' }}</span></td>
                        <td class="table-td text-right">
                            <button wire:click="editer({{ $type->id }})" class="text-blue-700 text-xs">تعديل</button>
                            <button wire:click="supprimer({{ $type->id }})" onclick="return confirm('حذف نوع المصروف هذا؟')" class="text-red-700 text-xs ml-2">حذف</button>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="table-td text-center text-gray-500">لا توجد أنواع مصروفات.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
