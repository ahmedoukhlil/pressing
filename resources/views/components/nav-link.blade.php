@props(['active'])

@php
$classes = ($active ?? false)
            ? 'inline-flex items-center rounded-lg px-3 py-2 text-sm font-medium bg-blue-50 text-blue-700'
            : 'inline-flex items-center rounded-lg px-3 py-2 text-sm font-medium text-slate-600 hover:bg-slate-100 hover:text-slate-900';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>
