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
</div>
