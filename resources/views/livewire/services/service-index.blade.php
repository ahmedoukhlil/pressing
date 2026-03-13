<div class="page-container">
    <div class="page-header">
        <div>
            <h1 class="page-title">الخدمات</h1>
            <p class="page-subtitle">قائمة الخدمات والأسعار.</p>
        </div>
        <a href="{{ route('services.create') }}" wire:navigate class="btn-primary">خدمة جديدة</a>
    </div>

    <div class="card card-body mb-4">
        <input wire:model.live.debounce.500ms="recherche" type="text" placeholder="ابحث عن خدمة" class="form-field md:max-w-md">
    </div>

    <div class="table-wrap">
        <table class="table-base">
            <thead class="table-head">
                <tr>
                    <th class="table-th w-12">الصورة</th>
                    <th class="table-th"><button wire:click="sortBy('libelle_ar')" class="font-medium">الاسم بالعربية</button></th>
                    <th class="table-th"><button wire:click="sortBy('prix')" class="font-medium">السعر</button></th>
                    <th class="table-th text-right"><button wire:click="sortBy('ordre')" class="font-medium">الترتيب</button></th>
                </tr>
            </thead>
            <tbody>
                @forelse($services as $service)
                    <tr class="table-row">
                        <td class="table-td">
                            @if($service->image)
                                <img src="{{ Storage::url($service->image) }}" alt="{{ $service->libelle_ar }}" class="h-8 w-8 rounded object-cover">
                            @else
                                <span class="text-lg">{{ $service->icone ?: '🧺' }}</span>
                            @endif
                        </td>
                        <td class="table-td">{{ $service->libelle_ar ?: '-' }}</td>
                        <td class="table-td"><span class="num-ltr">{{ number_format((float) $service->prix, 2, ',', ' ') }} MRU</span></td>
                        <td class="table-td text-right"><a href="{{ route('services.edit', $service->id) }}" wire:navigate class="text-blue-700 text-xs">تعديل</a></td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="table-td text-center text-gray-500">لا توجد خدمات.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-3">{{ $services->links() }}</div>
</div>
