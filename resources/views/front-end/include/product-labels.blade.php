@php
    $hasSale = $product->is_on_sale;
    $displayLabels = ($product->labels ?? collect())
        ->reject(fn ($label) => $label->type === 'sale' && $hasSale)
        ->take(3);
    $labelOffset = $product->stock <= 0 || $hasSale ? 43 : 12;
@endphp
@foreach($displayLabels as $productLabel)
    <span class="p-badge label-badge badge-{{ $productLabel->type }}" style="top:{{ $labelOffset + (($loop->index) * 31) }}px">
        {{ $productLabel->name }}
    </span>
@endforeach
