<div class="page-container">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <h1 class="text-2xl font-semibold">المستخدمون</h1>
        <a href="{{ route('admin.users.create') }}" wire:navigate class="btn-primary">مستخدم جديد</a>
    </div>

    <div class="card card-body mt-4 space-y-4">
        <div>
            <input type="text" wire:model.live.debounce.300ms="search" placeholder="ابحث بالاسم أو البريد الإلكتروني..." class="form-field max-w-md">
        </div>

        <div class="table-wrap">
            <table class="table-base">
                <thead class="table-head">
                    <tr>
                        <th class="table-th">الاسم</th>
                        <th class="table-th">البريد الإلكتروني</th>
                        <th class="table-th">الدور</th>
                        <th class="table-th">الفرع</th>
                        <th class="table-th w-24">إجراء</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($users as $user)
                        <tr class="table-row">
                            <td class="table-td">{{ $user->name }}</td>
                            <td class="table-td">{{ $user->email }}</td>
                            <td class="table-td">{{ ucfirst(optional($user->roles->first())->name ?? '-') }}</td>
                            <td class="table-td">{{ $user->succursale?->nom ?? '-' }}</td>
                            <td class="table-td">
                                <a href="{{ route('admin.users.edit', $user->id) }}" wire:navigate class="text-blue-700 hover:underline">تعديل</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="table-td text-center text-gray-500">لا يوجد مستخدمون.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div>{{ $users->links() }}</div>
    </div>

    <div class="card card-body mt-6 space-y-4">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <h2 class="text-lg font-semibold text-slate-900">الأدوار والصلاحيات (RBAC)</h2>
                <p class="text-xs text-slate-500">إدارة الأدوار والصلاحيات من نفس شاشة المستخدمين.</p>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('admin.roles.create') }}" wire:navigate class="btn-primary text-xs">دور جديد</a>
                <a href="{{ route('admin.roles.index') }}" wire:navigate class="btn-secondary text-xs">عرض كل الأدوار</a>
            </div>
        </div>

        <div class="grid lg:grid-cols-2 gap-4">
            <div class="table-wrap">
                <table class="table-base">
                    <thead class="table-head">
                        <tr>
                            <th class="table-th">الدور</th>
                            <th class="table-th">عدد الصلاحيات</th>
                            <th class="table-th text-right">إجراء</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($roles as $role)
                            <tr class="table-row">
                                <td class="table-td">{{ $role->name }}</td>
                                <td class="table-td">{{ $role->permissions_count }}</td>
                                <td class="table-td text-right">
                                    <a href="{{ route('admin.roles.edit', $role->id) }}" wire:navigate class="btn-ghost !px-2.5 !py-1.5 !text-xs text-blue-700">
                                        <i class="fi fi-rr-edit mr-1"></i> تعديل
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="table-td text-center text-gray-500">لا توجد أدوار معرفة.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="rounded-xl border border-slate-200 bg-white p-4">
                <h3 class="text-sm font-semibold text-slate-800 mb-3">كل الصلاحيات المتاحة</h3>
                <div class="flex flex-wrap gap-2">
                    @forelse($permissions as $permission)
                        <span class="inline-flex items-center rounded-full bg-slate-100 px-2.5 py-1 text-xs text-slate-700">
                            {{ $permission->name }}
                        </span>
                    @empty
                        <p class="text-xs text-slate-500">لا توجد صلاحيات.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>
