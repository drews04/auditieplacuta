@props([
    // Pass any paginator: LengthAwarePaginator or Paginator
    'paginator',
    // Use the compact previous/next only template if true
    'simple' => false,
    // Optional anchor (e.g. "replies" → links end with #replies)
    'fragment' => null,
])

@if ($paginator)
    @php
        if ($fragment) {
            // add #fragment to all links
            $paginator->fragment($fragment);
        }
    @endphp

    @if ($paginator->hasPages())
        @if ($simple)
            {{ $paginator->links('vendor.pagination.neon-simple') }}
        @else
            {{ $paginator->links('vendor.pagination.neon') }}
        @endif
    @endif
@endif
