@props(['status'])

@php
    $map = [
        'DITINJAU' => ['label' => 'Ditinjau', 'class' => 'bg-amber-100 text-amber-800', 'icon' => 'fa-solid fa-spinner animate-spin'],
        'PENDING' => ['label' => 'Perlu Revisi', 'class' => 'bg-amber-100 text-amber-800', 'icon' => 'fa-solid fa-spinner animate-spin'],
        'SIAP_TTD' => ['label' => 'Siap TTD', 'class' => 'bg-blue-100 text-blue-800', 'icon' => null],
        'DISETUJUI_TANPA_TTD' => ['label' => 'Perlu Tempel TTD', 'class' => 'bg-purple-100 text-purple-800', 'icon' => 'fa-solid fa-pen-nib'],
        'DISETUJUI' => ['label' => 'Disetujui', 'class' => 'bg-emerald-100 text-emerald-800', 'icon' => null],
        'DITOLAK' => ['label' => 'Ditolak', 'class' => 'bg-rose-100 text-rose-800', 'icon' => null],
    ];

    $item = $map[$status] ?? ['label' => $status, 'class' => 'bg-slate-100 text-slate-600', 'icon' => null];
@endphp

<span {{ $attributes->merge(['class' => "font-bold text-[10px] px-2.5 py-0.5 rounded-full uppercase tracking-wider inline-flex items-center {$item['class']}"]) }}>
    @if($item['icon'])
        <i class="{{ $item['icon'] }} mr-1"></i>
    @endif
    {{ $item['label'] }}
</span>