<div class="page-container">
    <div class="page-header">
        <div>
            <h1 class="page-title">{{ $clientId ? 'تعديل زبون' : 'زبون جديد' }}</h1>
            <p class="page-subtitle">بيانات الزبون للإضافة أو التحديث.</p>
        </div>
    </div>

    @if($errors->any())
        <div class="alert alert-error mb-4">
            يرجى تصحيح الحقول التي تحتوي على أخطاء.
        </div>
    @endif

    <form wire:submit.prevent="sauvegarder" class="card card-body grid md:grid-cols-2 gap-3">
        <div>
            <label class="form-label">الاسم <span class="text-red-500">*</span></label>
            <input wire:model.live="nom" type="text" placeholder="الاسم" class="form-field">
            @error('nom') <div class="form-error">{{ $message }}</div> @enderror
        </div>
        <div>
            <label class="form-label">اللقب <span class="text-slate-400 text-xs font-normal">(اختياري)</span></label>
            <input wire:model.live="prenom" type="text" placeholder="اللقب" class="form-field">
        </div>
        <div>
            <label class="form-label">الهاتف *</label>
            <input wire:model.live="telephone" type="tel" placeholder="الهاتف" class="form-field">
            @error('telephone') <div class="form-error">{{ $message }}</div> @enderror
        </div>
        <div>
            <label class="form-label">البريد الإلكتروني</label>
            <input wire:model.live="email" type="email" placeholder="البريد الإلكتروني" class="form-field">
            @error('email') <div class="form-error">{{ $message }}</div> @enderror
        </div>
        <div class="md:col-span-2">
            <label class="form-label">العنوان</label>
            <textarea wire:model.live="adresse" rows="3" placeholder="العنوان" class="form-field"></textarea>
        </div>
        <div class="md:col-span-2 flex gap-2">
            <button type="submit" class="btn-primary" wire:loading.attr="disabled">حفظ</button>
            <a href="{{ route('clients.index') }}" class="btn-secondary">رجوع</a>
        </div>
    </form>
</div>
