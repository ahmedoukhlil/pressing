<section class="space-y-6">
    <header>
        <h2 class="text-lg font-medium text-gray-900">
            حذف الحساب
        </h2>

        <p class="mt-1 text-sm text-gray-600">
            عند حذف حسابك، سيتم حذف جميع بياناته بشكل نهائي. قبل المتابعة، يرجى الاحتفاظ بأي معلومات تحتاجها.
        </p>
    </header>

    <x-danger-button
        x-data=""
        x-on:click.prevent="$dispatch('open-modal', 'confirm-user-deletion')"
    >حذف الحساب</x-danger-button>

    <x-modal name="confirm-user-deletion" :show="$errors->userDeletion->isNotEmpty()" focusable>
        <form method="post" action="{{ route('profile.destroy') }}" class="p-6">
            @csrf
            @method('delete')

            <h2 class="text-lg font-medium text-gray-900">
                هل أنت متأكد من رغبتك في حذف حسابك؟
            </h2>

            <p class="mt-1 text-sm text-gray-600">
                بعد الحذف، سيتم حذف جميع بيانات الحساب نهائيًا. أدخل كلمة المرور لتأكيد العملية.
            </p>

            <div class="mt-6">
                <x-input-label for="password" value="كلمة المرور" class="sr-only" />

                <x-text-input
                    id="password"
                    name="password"
                    type="password"
                    class="mt-1 block w-3/4"
                    placeholder="كلمة المرور"
                />

                <x-input-error :messages="$errors->userDeletion->get('password')" class="mt-2" />
            </div>

            <div class="mt-6 flex justify-end">
                <x-secondary-button x-on:click="$dispatch('close')">
                    إلغاء
                </x-secondary-button>

                <x-danger-button class="ms-3">
                    حذف الحساب
                </x-danger-button>
            </div>
        </form>
    </x-modal>
</section>
