<div class="page-container">
    <div class="page-header">
        <div>
            <h1 class="page-title">طرق الدفع</h1>
            <p class="page-subtitle">تهيئة طرق الدفع المستخدمة في التحصيل والمصروفات.</p>
        </div>
        <button wire:click="nouveauMode" class="btn-primary">جديد</button>
    </div>

    @if($afficherForm)
        <form wire:submit.prevent="sauvegarder" class="card card-body mb-4 grid md:grid-cols-4 gap-3">
            <div>
                <label class="form-label">الاسم *</label>
                <input wire:model.live="libelle" type="text" placeholder="اسم الطريقة" class="form-field">
                @error('libelle') <div class="form-error">{{ $message }}</div> @enderror
            </div>
            <div>
                <label class="form-label">الأيقونة</label>
                <input wire:model.live="icone" type="text" placeholder="الأيقونة" class="form-field">
            </div>
            <div>
                <label class="form-label">الترتيب</label>
                <input wire:model.live="ordre" type="number" min="0" placeholder="الترتيب" class="form-field">
                @error('ordre') <div class="form-error">{{ $message }}</div> @enderror
            </div>
            <div class="flex items-end">
                <label class="inline-flex items-center gap-2 text-sm"><input wire:model.live="actif" type="checkbox" class="rounded border-slate-300"> نشط</label>
            </div>
            <div class="md:col-span-5 flex gap-2">
                <button type="submit" class="btn-primary" wire:loading.attr="disabled">حفظ</button>
                <button type="button" wire:click="$set('afficherForm', false)" class="btn-secondary">إلغاء</button>
            </div>
        </form>
    @endif

    <div class="table-wrap">
        <table class="table-base">
            <thead class="table-head">
            <tr>
                <th class="table-th">الاسم</th>
                <th class="table-th">الترتيب</th>
                <th class="table-th">الحالة</th>
                <th class="table-th text-right">الإجراءات</th>
            </tr>
            </thead>
            <tbody>
            @forelse($modes as $mode)
                <tr class="table-row">
                    <td class="table-td">{{ $mode->icone }} {{ $mode->libelle }}</td>
                    <td class="table-td">{{ $mode->ordre }}</td>
                    <td class="table-td">
                        <span class="status-badge {{ $mode->actif ? 'status-success' : 'status-neutral' }}">
                            {{ $mode->actif ? 'نشط' : 'غير نشط' }}
                        </span>
                    </td>
                    <td class="table-td text-right">
                        <button wire:click="editer({{ $mode->id }})" class="btn-ghost !px-2.5 !py-1.5 !text-xs text-blue-700">
                            <i class="fi fi-rr-edit mr-1"></i> تعديل
                        </button>
                        <button wire:click="demanderToggleActif({{ $mode->id }})" class="btn-ghost !px-2.5 !py-1.5 !text-xs text-orange-700">
                            <i class="fi fi-rr-power mr-1"></i> {{ $mode->actif ? 'تعطيل' : 'تفعيل' }}
                        </button>
                        @if(!$mode->est_systeme)
                            <button wire:click="demanderSuppression({{ $mode->id }})" class="btn-ghost !px-2.5 !py-1.5 !text-xs text-red-700 hover:!bg-red-50">
                                <i class="fi fi-rr-trash mr-1"></i> حذف
                            </button>
                        @else
                            <span class="text-xs text-gray-400 ml-2">نظام</span>
                        @endif
                    </td>
                </tr>
            @empty
                <tr><td colspan="4" class="table-td text-center text-gray-500">لا توجد طرق دفع.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>

    @if($modeActionId && $modeActionType !== '')
        <div class="modal-overlay flex items-center justify-center p-4">
            <div class="modal-panel max-w-md p-4 space-y-3">
                <div class="text-lg font-medium">
                    {{ $modeActionType === 'delete' ? 'تأكيد حذف طريقة الدفع' : 'تأكيد تغيير الحالة' }}
                </div>
                <p class="text-sm text-slate-600">
                    @if($modeActionType === 'delete')
                        هل تريد حذف طريقة الدفع المحددة؟
                    @else
                        هل تريد تغيير حالة طريقة الدفع المحددة؟
                    @endif
                </p>
                <div class="flex justify-end gap-2">
                    <button wire:click="annulerActionMode" class="btn-secondary">إلغاء</button>
                    <button wire:click="confirmerActionMode" class="{{ $modeActionType === 'delete' ? 'btn-danger' : 'btn-primary' }}">
                        تأكيد
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
