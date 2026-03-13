<div class="page-container">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <h1 class="text-2xl font-semibold">الأدوار</h1>
        <a href="{{ route('admin.roles.create') }}" wire:navigate class="btn-primary">دور جديد</a>
    </div>

    <div class="table-wrap mt-4">
        <table class="table-base">
            <thead class="table-head">
                <tr>
                    <th class="table-th">الدور</th>
                    <th class="table-th">عدد الصلاحيات</th>
                    <th class="table-th w-24">إجراء</th>
                </tr>
            </thead>
            <tbody>
                @forelse($roles as $role)
                    <tr class="table-row">
                        <td class="table-td">{{ $role->name }}</td>
                        <td class="table-td">{{ $role->permissions_count }}</td>
                        <td class="table-td">
                            <a href="{{ route('admin.roles.edit', $role->id) }}" wire:navigate class="text-blue-700 hover:underline">تعديل</a>
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
</div>
