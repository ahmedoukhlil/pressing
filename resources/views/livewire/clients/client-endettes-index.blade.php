<div class="page-container">
    <div class="page-header">
        <div>
            <h1 class="page-title">الزبناء المدينون</h1>
            <p class="page-subtitle">لائحة الزبناء الذين لديهم مبالغ متبقية للدفع.</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('clients.index') }}" wire:navigate class="btn-secondary">جميع الزبناء</a>
            <a href="{{ route('clients.create') }}" wire:navigate class="btn-primary">زبون جديد</a>
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
                            <div class="inline-flex items-center gap-2">
                                <a href="{{ route('clients.compte', $client->id) }}" wire:navigate class="btn-ghost !px-2.5 !py-1.5 !text-xs text-emerald-700">
                                    <i class="fi fi-rr-receipt mr-1"></i> تفاصيل الحساب
                                </a>
                                <a href="{{ route('clients.edit', $client->id) }}" wire:navigate class="btn-ghost !px-2.5 !py-1.5 !text-xs text-blue-700">
                                    <i class="fi fi-rr-edit mr-1"></i> تعديل
                                </a>
                                @role('gerant')
                                    <button wire:click="demanderSuppressionClient({{ $client->id }})" class="btn-ghost !px-2.5 !py-1.5 !text-xs text-red-600">
                                        <i class="fi fi-rr-trash mr-1"></i> حذف
                                    </button>
                                @endrole
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

    @if($afficherConfirmationSuppression)
        <div class="modal-overlay flex items-center justify-center p-4">
            <div class="modal-panel max-w-md p-4 space-y-3">
                <h3 class="text-base font-semibold text-slate-900">تأكيد حذف الزبون</h3>
                <p class="text-sm text-slate-600">
                    هل تريد حذف الزبون <strong>{{ $clientASupprimerNom }}</strong>؟
                </p>
                <p class="text-xs text-slate-500">
                    لا يمكن حذف الزبون إذا كان مرتبطًا بطلبات.
                </p>
                <div class="flex justify-end gap-2">
                    <button wire:click="annulerSuppressionClient" class="btn-secondary">إلغاء</button>
                    <button wire:click="confirmerSuppressionClient" class="btn-danger">حذف</button>
                </div>
            </div>
        </div>
    @endif
</div>
