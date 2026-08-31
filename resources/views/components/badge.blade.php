@props(['type' => 'default'])

@php
$classes = match($type) {
    't1' => 'bg-blue-50 text-blue-700 border-blue-200',
    't2' => 'bg-purple-50 text-purple-700 border-purple-200',
    'reassigned' => 'bg-amber-50 text-amber-700 border-amber-200',
    'success' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
    'danger' => 'bg-rose-50 text-rose-700 border-rose-200',
    default => 'bg-slate-100 text-slate-700 border-slate-200',
};
@endphp

<span {{ $attributes->merge(['class' => "inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-semibold border $classes"]) }}>
    {{ $slot }}
</span>
