<div class="page-container space-y-4">
    <div class="page-header">
        <div>
            <h1 class="page-title">إدارة الفروع</h1>
            <p class="page-subtitle">إنشاء الفروع وتفعيلها وربط المستخدمين بها.</p>
        </div>
    </div>

    <form wire:submit.prevent="sauvegarder" class="card card-body space-y-3">
        <div class="grid md:grid-cols-3 gap-3">
            <div>
                <label class="form-label">اسم الفرع *</label>
                <input wire:model.live="nom" type="text" class="form-field">
                @error('nom') <div class="form-error">{{ $message }}</div> @enderror
            </div>
            <div>
                <label class="form-label">رمز الفرع *</label>
                <input wire:model.live="code" type="text" class="form-field">
                @error('code') <div class="form-error">{{ $message }}</div> @enderror
            </div>
            <div class="flex items-end">
                <label class="inline-flex items-center gap-2 text-sm">
                    <input type="checkbox" wire:model.live="actif" class="rounded border-slate-300">
                    فرع نشط
                </label>
            </div>
        </div>
        <div class="flex justify-end gap-2">
            @if($editId)
                <button type="button" wire:click="annulerEdition" class="btn-secondary">إلغاء</button>
            @endif
            <button type="submit" class="btn-primary">
                {{ $editId ? 'تحديث' : 'إضافة فرع' }}
            </button>
        </div>
    </form>

    <div class="card card-body">
        <div class="table-wrap">
            <table class="table-base">
                <thead class="table-head">
                    <tr>
                        <th class="table-th">الاسم</th>
                        <th class="table-th">الرمز</th>
                        <th class="table-th">الحالة</th>
                        <th class="table-th">إجراء</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($succursales as $succursale)
                        <tr class="table-row">
                            <td class="table-td">{{ $succursale->nom }}</td>
                            <td class="table-td">{{ $succursale->code }}</td>
                            <td class="table-td">
                                @if($succursale->actif)
                                    <span class="status-badge status-success">نشط</span>
                                @else
                                    <span class="status-badge status-danger">معطل</span>
                                @endif
                            </td>
                            <td class="table-td space-x-2">
                                <button wire:click="editer({{ $succursale->id }})" class="text-blue-700 hover:underline">تعديل</button>
                                <button wire:click="basculerActif({{ $succursale->id }})" class="text-amber-700 hover:underline">تغيير الحالة</button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="table-td text-center text-slate-500">لا توجد فروع.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

