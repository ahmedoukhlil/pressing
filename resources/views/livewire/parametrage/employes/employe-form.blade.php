<div class="page-container">
    <div class="page-header">
        <div>
            <h1 class="page-title">{{ $employeId ? 'تعديل موظف' : 'موظف جديد' }}</h1>
            <p class="page-subtitle">أدخل بيانات الموارد البشرية الأساسية.</p>
        </div>
    </div>

    @if($errors->any())
        <div class="alert alert-error mb-4">يرجى تصحيح الحقول التي تحتوي على أخطاء.</div>
    @endif

    <form wire:submit.prevent="sauvegarder" class="card card-body grid md:grid-cols-2 gap-3">
        <div>
            <label class="form-label">الاسم *</label>
            <input wire:model.live="nom" type="text" placeholder="الاسم" class="form-field">
            @error('nom') <div class="form-error">{{ $message }}</div> @enderror
        </div>
        <div>
            <label class="form-label">الاسم الثاني</label>
            <input wire:model.live="prenom" type="text" placeholder="الاسم الثاني" class="form-field">
        </div>
        <div>
            <label class="form-label">الهاتف</label>
            <input wire:model.live="telephone" type="text" placeholder="الهاتف" class="form-field">
            @error('telephone') <div class="form-error">{{ $message }}</div> @enderror
        </div>
        <div>
            <label class="form-label">الوظيفة</label>
            <select wire:model.live="fkIdPoste" class="form-field">
                <option value="">الوظيفة</option>
                @foreach($postes as $poste)
                    <option value="{{ $poste->id }}">{{ $poste->libelle }}</option>
                @endforeach
            </select>
            @error('fkIdPoste') <div class="form-error">{{ $message }}</div> @enderror
        </div>
        <div>
            <label class="form-label">تاريخ التوظيف</label>
            <input wire:model.live="dateEmbauche" type="date" class="form-field">
            @error('dateEmbauche') <div class="form-error">{{ $message }}</div> @enderror
        </div>
        <div>
            <label class="form-label">الراتب الإجمالي *</label>
            <input wire:model.live="salaireBrut" type="number" step="0.01" min="0" placeholder="الراتب الإجمالي" class="form-field">
            @error('salaireBrut') <div class="form-error">{{ $message }}</div> @enderror
        </div>
        <div class="md:col-span-2">
            <label class="form-label">ملاحظات</label>
            <textarea wire:model.live="notes" rows="3" placeholder="ملاحظات" class="form-field"></textarea>
        </div>
        <div class="md:col-span-2 flex items-center justify-between gap-3">
            <label class="inline-flex items-center gap-2 text-sm"><input wire:model.live="actif" type="checkbox" class="rounded border-slate-300"> نشط</label>
            <div class="flex gap-2">
                <button type="submit" class="btn-primary" wire:loading.attr="disabled">حفظ</button>
                <a href="{{ route('parametrage.employes.index') }}" wire:navigate class="btn-secondary">رجوع</a>
            </div>
        </div>
    </form>
</div>
