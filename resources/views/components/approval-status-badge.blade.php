@props(['status', 'dot' => false])

@php
    $map = [
        'TERIMA' => ['label' => 'Diterima', 'class' => 'bg-emerald-50 text-emerald-700', 'dotClass' => 'bg-emerald-500'],
        'MENUNGGU' => ['label' => 'Menunggu', 'class' => 'bg-amber-100 text-amber-800', 'dotClass' => 'bg-amber-500 animate-pulse'],
        'PENDING' => ['label' => 'Menunggu', 'class' => 'bg-amber-100 text-amber-800', 'dotClass' => 'bg-amber-500 animate-pulse'],
        'TOLAK' => ['label' => 'Ditolak', 'class' => 'bg-rose-100 text-rose-800', 'dotClass' => 'bg-rose-500'],
        'DIBATALKAN' => ['label' => 'Dibatalkan', 'class' => 'bg-slate-100 text-slate-600', 'dotClass' => 'bg-slate-400'],
    ];

    $item = $map[$status] ?? ['label' => $status, 'class' => 'bg-slate-100 text-slate-600', 'dotClass' => 'bg-slate-400'];
@endphp

@if($dot)
    <div class="w-2 h-2 rounded-full {{ $item['dotClass'] }}"></div>
@else
    <span {{ $attributes->merge(['class' => "font-bold text-[10px] px-2 py-0.5 rounded {$item['class']}"]) }}>
        {{ $status }}
    </span>
@endif