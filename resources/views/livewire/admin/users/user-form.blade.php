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

        {{-- Filiales assignées (multi-sélection) --}}
        <div>
            <label class="form-label">الفروع المخصصة *</label>
            <div class="border border-gray-200 rounded-lg divide-y divide-gray-100">
                @foreach($succursales as $succursale)
                    <label class="flex items-center gap-3 px-4 py-2.5 hover:bg-gray-50 cursor-pointer">
                        <input
                            type="checkbox"
                            value="{{ $succursale->id }}"
                            wire:model.live="succursaleIds"
                            class="rounded border-gray-300 text-primary"
                        >
                        <span class="text-sm">{{ $succursale->nom }}</span>
                    </label>
                @endforeach
            </div>
            @error('succursaleIds') <div class="form-error">{{ $message }}</div> @enderror
        </div>

        {{-- Filiale principale (parmi les filiales cochées) --}}
        @if(count($succursaleIds) > 0)
        <div>
            <label class="form-label">الفرع الرئيسي *</label>
            <select wire:model.live="fkIdSuccursale" class="form-field">
                <option value="">اختر الفرع الرئيسي...</option>
                @foreach($succursales as $succursale)
                    @if(in_array($succursale->id, $succursaleIds))
                        <option value="{{ $succursale->id }}">{{ $succursale->nom }}</option>
                    @endif
                @endforeach
            </select>
            @error('fkIdSuccursale') <div class="form-error">{{ $message }}</div> @enderror
        </div>
        @endif

        <div class="flex justify-end gap-2">
            <a href="{{ route('admin.users.index') }}" wire:navigate class="btn-secondary">رجوع</a>
            <button type="submit" class="btn-primary" wire:loading.attr="disabled">حفظ</button>
        </div>
    </form>
</div>
