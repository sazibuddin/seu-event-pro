@php
    $colors = [
        'published' => 'bg-green-100 text-green-800',
        'confirmed' => 'bg-green-100 text-green-800',
        'draft' => 'bg-gray-100 text-gray-700',
        'attended' => 'bg-blue-100 text-blue-800',
        'cancelled' => 'bg-red-100 text-red-800',
    ];
@endphp
<span class="inline-flex rounded-full px-2.5 py-0.5 text-xs font-semibold capitalize {{ $colors[$status] ?? 'bg-gray-100 text-gray-700' }}">
    {{ $status }}
</span>
