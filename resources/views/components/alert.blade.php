@props(['type' => 'success', 'message' => ''])

@php
$styles = match($type) {
    'error' => ['bg' => 'bg-rose-50 border-rose-200 text-rose-800', 'icon' => 'ph-warning-circle'],
    'warning' => ['bg' => 'bg-amber-50 border-amber-200 text-amber-800', 'icon' => 'ph-warning'],
    default => ['bg' => 'bg-emerald-50 border-emerald-200 text-emerald-800', 'icon' => 'ph-check-circle'],
};
@endphp

<div class="flex items-center gap-3 p-4 rounded-xl border {{ $styles['bg'] }} mb-4 shadow-sm">
    <i class="ph {{ $styles['icon'] }} text-xl flex-shrink-0"></i>
    <p class="text-sm font-medium">{{ $slot->isEmpty() ? $message : $slot }}</p>
</div>
