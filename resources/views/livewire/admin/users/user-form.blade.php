<div class="page-container">
    <h1 class="text-2xl font-semibold">{{ $userId ? 'تعديل مستخدم' : 'مستخدم جديد' }}</h1>

    <form wire:submit.prevent="sauvegarder" class="card card-body mt-6 max-w-3xl space-y-4">
        <div class="grid md:grid-cols-2 gap-4">
            <div>
                <label class="form-label">الاسم الكامل *</label>
                <input type="text" wire:model.live="name" class="form-field">
                @error('name') <div class="form-error">{{ $message }}</div> @enderror
            </div>
            <div>
                <label class="form-label">البريد الإلكتروني *</label>
                <input type="email" wire:model.live="email" class="form-field">
                @error('email') <div class="form-error">{{ $message }}</div> @enderror
            </div>
        </div>

        <div class="grid md:grid-cols-2 gap-4">
            <div>
                <label class="form-label">
                    {{ $userId ? 'كلمة مرور جديدة (اختياري)' : 'كلمة المرور *' }}
                </label>
                <input type="password" wire:model.live="password" class="form-field">
                @error('password') <div class="form-error">{{ $message }}</div> @enderror
            </div>
            <div>
                <label class="form-label">الدور *</label>
                <select wire:model.live="role" class="form-field">
                    <option value="">اختر...</option>
                    @foreach($roles as $r)
                        <option value="{{ $r->name }}">{{ ucfirst($r->name) }}</option>
                    @endforeach
                </select>
                @error('role') <div class="form-error">{{ $message }}</div> @enderror
            </div>
        </div>

        <div>
            <label class="form-label">الفرع *</label>
            <select wire:model.live="fkIdSuccursale" class="form-field">
                <option value="">اختر الفرع...</option>
                @foreach($succursales as $succursale)
                    <option value="{{ $succursale->id }}">{{ $succursale->nom }}</option>
                @endforeach
            </select>
            @error('fkIdSuccursale') <div class="form-error">{{ $message }}</div> @enderror
        </div>

        <div class="flex justify-end gap-2">
            <a href="{{ route('admin.users.index') }}" wire:navigate class="btn-secondary">رجوع</a>
            <button type="submit" class="btn-primary" wire:loading.attr="disabled">حفظ</button>
        </div>
    </form>
</div>
