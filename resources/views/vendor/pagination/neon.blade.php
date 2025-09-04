@if ($paginator->hasPages())
<nav class="ap-pagination" role="navigation" aria-label="Pagination">
  <ul class="pagination neon-pagination">

    {{-- Previous --}}
    @if ($paginator->onFirstPage())
      <li class="page-item prev disabled" aria-disabled="true" aria-label="@lang('pagination.previous')">
        <span class="page-link neon-page">&lsaquo;</span>
      </li>
    @else
      <li class="page-item prev">
        <a class="page-link neon-page" href="{{ $paginator->previousPageUrl() }}" rel="prev" aria-label="@lang('pagination.previous')">&lsaquo;</a>
      </li>
    @endif

    {{-- Numbers --}}
    @foreach ($elements as $element)
      @if (is_string($element))
        <li class="page-item disabled"><span class="page-link neon-page">{{ $element }}</span></li>
      @endif
      @if (is_array($element))
        @foreach ($element as $page => $url)
          @if ($page == $paginator->currentPage())
            <li class="page-item active"><span class="page-link neon-page is-active">{{ $page }}</span></li>
          @else
            <li class="page-item"><a class="page-link neon-page" href="{{ $url }}">{{ $page }}</a></li>
          @endif
        @endforeach
      @endif
    @endforeach

    {{-- Next --}}
    @if ($paginator->hasMorePages())
      <li class="page-item next">
        <a class="page-link neon-page" href="{{ $paginator->nextPageUrl() }}" rel="next" aria-label="@lang('pagination.next')">&rsaquo;</a>
      </li>
    @else
      <li class="page-item next disabled" aria-disabled="true" aria-label="@lang('pagination.next')">
        <span class="page-link neon-page">&rsaquo;</span>
      </li>
    @endif

  </ul>
</nav>
@endif
