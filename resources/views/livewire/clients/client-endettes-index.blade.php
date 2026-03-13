<div class="page-container">
    <div class="page-header">
        <div>
            <h1 class="page-title">الزبناء المدينون</h1>
            <p class="page-subtitle">لائحة الزبناء الذين لديهم مبالغ متبقية للدفع.</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('clients.index') }}" class="btn-secondary">جميع الزبناء</a>
            <a href="{{ route('clients.create') }}" class="btn-primary">زبون جديد</a>
        </div>
    </div>

    <div class="card card-body mb-4">
        <input wire:model.live.debounce.500ms="recherche" type="text" placeholder="ابحث بالاسم أو رقم الهاتف" class="form-field md:max-w-md">
    </div>

    <div class="table-wrap">
        <table class="table-base">
            <thead class="table-head">
                <tr>
                    <th class="table-th">الاسم</th>
                    <th class="table-th">الهاتف</th>
                    <th class="table-th">إجمالي الدين</th>
                    <th class="table-th text-right">الإجراءات</th>
                </tr>
            </thead>
            <tbody>
                @forelse($clients as $client)
                    <tr class="table-row">
                        <td class="table-td">{{ $client->full_name }}</td>
                        <td class="table-td">{{ $client->telephone }}</td>
                        <td class="table-td text-amber-700 font-semibold">{{ number_format((float) ($client->total_dette ?? 0), 2) }} MRU</td>
                        <td class="table-td text-right">
                            <div class="inline-flex items-center gap-3">
                                <a href="{{ route('clients.compte', $client->id) }}" class="text-emerald-700 text-xs">تفاصيل الحساب</a>
                                <a href="{{ route('clients.edit', $client->id) }}" class="text-blue-700 text-xs">تعديل</a>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="table-td text-center text-gray-500">لا يوجد زبناء مدينون.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-3">{{ $clients->links() }}</div>
</div>
