<div class="page-container">
    <div class="page-header">
        <div>
            <h1 class="page-title">الموظفون</h1>
            <p class="page-subtitle">إدارة الموظفين والوظائف والوصول إلى السلف.</p>
        </div>
        <a href="{{ route('parametrage.employes.create') }}" class="btn-primary">موظف جديد</a>
    </div>

    <div class="card card-body mb-4">
        <input wire:model.live.debounce.400ms="search" type="text" class="form-field md:max-w-md" placeholder="ابحث بالاسم أو الهاتف">
    </div>

    <div class="table-wrap">
        <table class="table-base">
            <thead class="table-head">
                <tr>
                    <th class="table-th">الموظف</th>
                    <th class="table-th">الوظيفة</th>
                    <th class="table-th">الراتب الإجمالي</th>
                    <th class="table-th text-right">الإجراءات</th>
                </tr>
            </thead>
            <tbody>
                @forelse($employes as $e)
                    <tr class="table-row">
                        <td class="table-td">{{ $e->full_name }}</td>
                        <td class="table-td">{{ $e->poste?->libelle ?? '-' }}</td>
                        <td class="table-td"><span class="num-ltr">{{ number_format((float) $e->salaire_brut, 2, ',', ' ') }} MRU</span></td>
                        <td class="table-td text-right">
                            <a href="{{ route('parametrage.employes.edit', $e->id) }}" class="text-blue-700 text-xs">تعديل</a>
                            <a href="{{ route('parametrage.employes.avances', $e->id) }}" class="text-green-700 text-xs ml-2">السلف</a>
                            <a href="{{ route('parametrage.employes.paiement', $e->id) }}" class="text-indigo-700 text-xs ml-2">دفع الرواتب</a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="table-td text-center text-gray-500">لا يوجد موظفون.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-3">{{ $employes->links() }}</div>
</div>
