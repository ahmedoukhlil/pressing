<div class="page-container">
    <h1 class="text-2xl font-semibold">{{ $roleId ? 'تعديل دور' : 'دور جديد' }}</h1>

    <form wire:submit.prevent="sauvegarder" class="card card-body mt-6 max-w-4xl space-y-4">
        <div>
            <label class="form-label">اسم الدور *</label>
            <input type="text" wire:model.live="name" class="form-field">
            @error('name') <div class="form-error">{{ $message }}</div> @enderror
        </div>

        <div>
            <label class="block text-sm font-medium mb-2">صلاحيات الوصول إلى قوائم الشريط الجانبي</label>
            @php($permissionLabels = config('rbac.permission_labels', []))
            @php($sidebarPermissions = config('rbac.sidebar_view_permissions', []))
            <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-2">
                @foreach($permissions as $permission)
                    @continue(!in_array($permission->name, $sidebarPermissions, true))
                    @php($permissionLabel = $permissionLabels[$permission->name] ?? $permission->name)
                    <label class="inline-flex items-center gap-2 rounded-lg border border-slate-200 px-3 py-2">
                        <input type="checkbox" value="{{ $permission->name }}" wire:model.live="selectedPermissions">
                        <span class="text-sm">
                            {{ $permissionLabel }}
                        </span>
                    </label>
                @endforeach
            </div>
            @error('selectedPermissions.*') <div class="form-error">{{ $message }}</div> @enderror
        </div>

        <div class="flex justify-end gap-2">
            <a href="{{ route('admin.roles.index') }}" wire:navigate class="btn-secondary">رجوع</a>
            <button type="submit" class="btn-primary" wire:loading.attr="disabled">حفظ</button>
        </div>
    </form>
</div>
