<x-guest-layout>
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <div class="mb-5 text-center">
        <h1 class="text-xl font-bold text-slate-900">تسجيل الدخول</h1>
        <p class="mt-1 text-sm text-slate-500">أدخل بياناتك للوصول إلى لوحة {{ config('app.name') }}.</p>
    </div>

    <form method="POST" action="{{ route('login') }}" class="space-y-4">
        @csrf

        <div>
            <x-input-label for="email" value="البريد الإلكتروني" />
            <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required autofocus autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="password" value="كلمة المرور" />

            <x-text-input id="password" class="block mt-1 w-full"
                            type="password"
                            name="password"
                            required autocomplete="current-password" />

            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <div class="block">
            <label for="remember_me" class="inline-flex items-center gap-2">
                <input id="remember_me" type="checkbox" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500" name="remember">
                <span class="text-sm text-gray-600">تذكرني</span>
            </label>
        </div>

        <div class="flex items-center justify-between pt-1">
            @if (Route::has('password.request'))
                <a class="text-sm text-blue-700 hover:underline rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500" href="{{ route('password.request') }}">
                    هل نسيت كلمة المرور؟
                </a>
            @endif

            <x-primary-button>
                دخول
            </x-primary-button>
        </div>
    </form>
</x-guest-layout>
