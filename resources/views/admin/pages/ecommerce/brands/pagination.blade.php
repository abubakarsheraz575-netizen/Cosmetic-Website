@php
    $firstPage = max(1, $paginator->currentPage() - 2);
    $lastVisiblePage = min($paginator->lastPage(), $paginator->currentPage() + 2);
@endphp
<div class="brand-pagination">
    <p>Showing {{ $paginator->firstItem() ?? 0 }} to {{ $paginator->lastItem() ?? 0 }} of {{ number_format($paginator->total()) }} {{ $itemLabel }}</p>
    <nav aria-label="{{ ucfirst($itemLabel) }} pages" class="brand-pagination-links">
        @if($paginator->onFirstPage())
            <span class="brand-page disabled" aria-disabled="true">Previous</span>
        @else
            <a class="brand-page" href="{{ $paginator->previousPageUrl() }}" rel="prev">Previous</a>
        @endif

        @if($firstPage > 1)
            <a class="brand-page" href="{{ $paginator->url(1) }}">1</a>
            @if($firstPage > 2)<span class="brand-page-gap" aria-hidden="true">…</span>@endif
        @endif

        @for($page = $firstPage; $page <= $lastVisiblePage; $page++)
            @if($page === $paginator->currentPage())
                <span class="brand-page active" aria-current="page">{{ $page }}</span>
            @else
                <a class="brand-page" href="{{ $paginator->url($page) }}">{{ $page }}</a>
            @endif
        @endfor

        @if($lastVisiblePage < $paginator->lastPage())
            @if($lastVisiblePage < $paginator->lastPage() - 1)<span class="brand-page-gap" aria-hidden="true">…</span>@endif
            <a class="brand-page" href="{{ $paginator->url($paginator->lastPage()) }}">{{ $paginator->lastPage() }}</a>
        @endif

        @if($paginator->hasMorePages())
            <a class="brand-page" href="{{ $paginator->nextPageUrl() }}" rel="next">Next</a>
        @else
            <span class="brand-page disabled" aria-disabled="true">Next</span>
        @endif
    </nav>
</div>
