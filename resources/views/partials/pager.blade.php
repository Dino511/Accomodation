{{-- Previous / Next under a table. Use it with {{ $rows->links('partials.pager') }}. Hidden when everything fits on one page --}}
@if ($paginator->hasPages())
    <div class="pager">
        <span class="muted">Showing {{ $paginator->firstItem() }}–{{ $paginator->lastItem() }} of {{ $paginator->total() }}</span>

        <div class="actions">
            @if ($paginator->onFirstPage())
                <a class="btn small disabled" aria-disabled="true">‹ Previous</a>
            @else
                <a class="btn small" href="{{ $paginator->previousPageUrl() }}">‹ Previous</a>
            @endif

            <span class="muted">Page {{ $paginator->currentPage() }} of {{ $paginator->lastPage() }}</span>

            @if ($paginator->hasMorePages())
                <a class="btn small" href="{{ $paginator->nextPageUrl() }}">Next ›</a>
            @else
                <a class="btn small disabled" aria-disabled="true">Next ›</a>
            @endif
        </div>
    </div>
@endif
