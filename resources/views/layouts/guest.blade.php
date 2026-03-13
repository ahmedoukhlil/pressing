<!DOCTYPE html>
<html lang="ar" dir="rtl" class="h-full bg-slate-100">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'مغسلة النظيف') }}</title>
    <style>
        .num-ltr {
            direction: ltr;
            unicode-bidi: isolate;
            display: inline-block;
            text-align: left;
        }
    </style>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="app-shell">
    <div class="min-h-screen flex items-center justify-center p-4">
        <div class="w-full max-w-md">
            <div class="flex justify-center mb-5">
                <a href="{{ route('dashboard') }}" class="inline-flex items-center gap-2 rounded-lg bg-slate-900 px-3 py-2 text-sm font-semibold text-white">
                    <span class="inline-flex h-7 w-7 items-center justify-center rounded bg-blue-600">م</span>
                    <span>{{ config('app.name') }}</span>
                </a>
            </div>
            <div class="card card-body">
                {{ $slot }}
            </div>
        </div>
    </div>
</body>
</html>
