<div class="page-container">
    <div class="page-header">
        <div>
            <h1 class="page-title">الموردون</h1>
            <p class="page-subtitle">إدارة الموردين وحالة التفعيل.</p>
        </div>
        <button wire:click="nouveau" class="btn-primary">جديد</button>
    </div>

    <div class="card card-body mb-4 flex gap-2">
        <input wire:model.live.debounce.500ms="recherche" type="text" placeholder="بحث..." class="form-field w-full">
        <select wire:model.live="filtreActif" class="form-field max-w-[180px]">
            <option value="tous">الكل</option>
            <option value="actif">نشط</option>
            <option value="inactif">غير نشط</option>
        </select>
    </div>

    @if($afficherForm)
        <form wire:submit.prevent="sauvegarder" class="card card-body mb-4 grid md:grid-cols-4 gap-3">
            <div>
                <label class="form-label">الاسم *</label>
                <input wire:model.live="nom" type="text" placeholder="الاسم" class="form-field">
                @error('nom') <div class="form-error">{{ $message }}</div> @enderror
            </div>
            <div>
                <label class="form-label">الهاتف *</label>
                <input wire:model.live="telephone" type="text" placeholder="الهاتف" class="form-field">
                @error('telephone') <div class="form-error">{{ $message }}</div> @enderror
            </div>
            <div>
                <label class="form-label">الرقم الضريبي</label>
                <input wire:model.live="nif" type="text" placeholder="الرقم الضريبي" class="form-field">
                @error('nif') <div class="form-error">{{ $message }}</div> @enderror
            </div>
            <div class="flex items-end">
                <label class="inline-flex items-center gap-2 text-sm"><input wire:model.live="actif" type="checkbox" class="rounded border-slate-300"> نشط</label>
            </div>
            <div class="md:col-span-4 flex gap-2">
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
                    <th class="table-th">الهاتف</th>
                    <th class="table-th">الرقم الضريبي</th>
                    <th class="table-th">الحالة</th>
                    <th class="table-th text-right">الإجراءات</th>
                </tr>
            </thead>
            <tbody>
                @forelse($fournisseurs as $f)
                    <tr class="table-row">
                        <td class="table-td">{{ $f->nom }}</td>
                        <td class="table-td">{{ $f->telephone }}</td>
                        <td class="table-td">{{ $f->nif ?: '-' }}</td>
                        <td class="table-td">
                            <span class="status-badge {{ $f->actif ? 'status-success' : 'status-neutral' }}">{{ $f->actif ? 'نشط' : 'غير نشط' }}</span>
                        </td>
                        <td class="table-td text-right">
                            <button wire:click="editer({{ $f->id }})" class="text-blue-700 text-xs">تعديل</button>
                            <button wire:click="toggleActif({{ $f->id }})" onclick="return confirm('تأكيد تغيير الحالة؟')" class="text-orange-700 text-xs ml-2">{{ $f->actif ? 'تعطيل' : 'تفعيل' }}</button>
                            <button wire:click="supprimer({{ $f->id }})" onclick="return confirm('حذف هذا المورد؟')" class="text-red-700 text-xs ml-2">حذف</button>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="table-td text-center text-gray-500">لا يوجد موردون.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-3">{{ $fournisseurs->links() }}</div>
</div>
