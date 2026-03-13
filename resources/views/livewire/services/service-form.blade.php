<div class="page-container">
    <div class="page-header">
        <div>
            <h1 class="page-title">{{ $serviceId ? 'تعديل خدمة' : 'خدمة جديدة' }}</h1>
            <p class="page-subtitle">قم بإعداد الاسم والسعر وحالة الخدمة.</p>
        </div>
    </div>

    @if($errors->any())
        <div class="alert alert-error mb-4">يرجى تصحيح الحقول التي تحتوي على أخطاء.</div>
    @endif

    <form wire:submit.prevent="sauvegarder" class="card card-body grid md:grid-cols-2 gap-3">
        <div>
            <label class="form-label">الاسم بالعربية *</label>
            <input wire:model.live="libelleAr" type="text" placeholder="الاسم بالعربية" class="form-field">
            @error('libelleAr') <div class="form-error">{{ $message }}</div> @enderror
        </div>
        <div>
            <label class="form-label">الاسم (اختياري)</label>
            <input wire:model.live="libelle" type="text" placeholder="Nom du service (optionnel)" class="form-field">
            @error('libelle') <div class="form-error">{{ $message }}</div> @enderror
        </div>

        <div class="md:col-span-2">
            <label class="form-label">صورة الخدمة</label>
            <div class="flex items-center gap-4">
                @if($nouvelleImage)
                    <img src="{{ $nouvelleImage->temporaryUrl() }}" class="h-16 w-16 rounded-lg object-cover border border-slate-200" alt="معاينة">
                @elseif($imageActuelle)
                    <img src="{{ Storage::url($imageActuelle) }}" class="h-16 w-16 rounded-lg object-cover border border-slate-200" alt="صورة حالية">
                @endif
                <div class="flex-1">
                    <input wire:model="nouvelleImage" type="file" accept="image/*" class="block w-full text-xs text-slate-500 file:mr-3 file:rounded-md file:border-0 file:bg-blue-50 file:px-3 file:py-1.5 file:text-xs file:font-medium file:text-blue-700 hover:file:bg-blue-100">
                    <div wire:loading wire:target="nouvelleImage" class="text-xs text-blue-600 mt-1">جاري الرفع...</div>
                    @error('nouvelleImage') <div class="form-error">{{ $message }}</div> @enderror
                </div>
                @if($imageActuelle && !$nouvelleImage)
                    <button type="button" wire:click="supprimerImage" class="text-xs text-red-500 hover:text-red-700">حذف الصورة</button>
                @endif
            </div>
        </div>

        <div>
            <label class="form-label">الأيقونة (احتياطية)</label>
            <input wire:model.live="icone" type="text" placeholder="🧺" class="form-field">
        </div>
        <div>
            <label class="form-label">السعر *</label>
            <input wire:model.live="prix" type="number" step="0.01" min="0" placeholder="السعر" class="form-field">
            @error('prix') <div class="form-error">{{ $message }}</div> @enderror
        </div>
        <div>
            <label class="form-label">الترتيب</label>
            <input wire:model.live="ordre" type="number" min="0" placeholder="الترتيب" class="form-field">
            @error('ordre') <div class="form-error">{{ $message }}</div> @enderror
        </div>
        <div class="flex items-end">
            <label class="inline-flex items-center gap-2 text-xs text-slate-700"><input wire:model.live="actif" type="checkbox" class="rounded border-slate-300"> نشط</label>
        </div>
        <div class="md:col-span-2 flex gap-2">
            <button type="submit" class="btn-primary" wire:loading.attr="disabled">حفظ</button>
            <a href="{{ route('services.index') }}" wire:navigate class="btn-secondary">رجوع</a>
        </div>
    </form>
</div>
