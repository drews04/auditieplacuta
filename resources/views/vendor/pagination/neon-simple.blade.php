@if ($paginator->hasPages())
<nav class="ap-pagination" role="navigation" aria-label="Pagination">
    <ul class="pagination neon-pagination">
        {{-- Previous --}}
        @if ($paginator->onFirstPage())
            <li class="page-item disabled" aria-disabled="true"><span class="page-link neon-page">&lsaquo;</span></li>
        @else
            <li class="page-item"><a class="page-link neon-page" href="{{ $paginator->previousPageUrl() }}" rel="prev">&lsaquo;</a></li>
        @endif

        {{-- Page Counter --}}
        <li class="page-item disabled">
            <span class="page-link neon-page">{{ $paginator->currentPage() }} / {{ $paginator->lastPage() }}</span>
        </li>

        {{-- Next --}}
        @if ($paginator->hasMorePages())
            <li class="page-item"><a class="page-link neon-page" href="{{ $paginator->nextPageUrl() }}" rel="next">&rsaquo;</a></li>
        @else
            <li class="page-item disabled" aria-disabled="true"><span class="page-link neon-page">&rsaquo;</span></li>
        @endif
    </ul>
</nav>
@endif
