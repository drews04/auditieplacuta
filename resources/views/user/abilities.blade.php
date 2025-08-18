@extends('layouts.app')

@section('content')
<div class="user-page-wrapper container py-5">
    <h1>🧙‍♂️ Abilități disponibile</h1>

    {{-- Abilități disponibile Card --}}
    <div class="card user-card mt-4 shadow-sm">
        <div class="row row-cols-2 row-cols-md-4 g-3 text-center px-3 pb-3">
            @forelse ($abilities as $ability)
                <div class="col">
                    <div class="p-3 rounded bg-dark text-white border border-info text-center h-100 d-flex flex-column justify-content-between">
                        {{-- Icon --}}
                        <div class="mb-2">
                        @switch($ability->code)
                                @case('steal')
                                    <svg width="28" height="28" fill="currentColor" class="text-danger" viewBox="0 0 24 24">
                                        <path d="M12 2c-2 0-4 2-4 4v2H6c-1.1 0-2 .9-2 2v1h16v-1c0-1.1-.9-2-2-2h-2V6c0-2-2-4-4-4zM4 12v8c0 1.1.9 2 2 2h2v-4h8v4h2c1.1 0 2-.9 2-2v-8H4z"/>
                                    </svg>
                                    @break

                                @case('shield')
                                    <svg width="28" height="28" fill="currentColor" class="text-success" viewBox="0 0 24 24">
                                        <path d="M12 2l8 4v6c0 5.25-3.25 10-8 12-4.75-2-8-6.75-8-12V6l8-4z"/>
                                    </svg>
                                    @break

                                @case('freeze')
                                    <svg width="28" height="28" fill="currentColor" class="text-primary" viewBox="0 0 24 24">
                                        <path d="M21 12h-6l4.24-4.24-1.41-1.41L13.17 12l4.66 5.66 1.41-1.41L15 12h6zM3 12h6l-4.24 4.24 1.41 1.41L10.83 12 6.17 6.34 4.76 7.75 9 12H3z"/>
                                    </svg>
                                    @break

                                @case('extra_vote')
                                    <svg width="28" height="28" fill="currentColor" class="text-info" viewBox="0 0 24 24">
                                        <path d="M12 2a10 10 0 100 20 10 10 0 000-20zm1 14h-2v-2H9v-2h2V9h2v2h2v2h-2v2z"/>
                                    </svg>
                                    @break

                                @case('block')
                                    <svg width="28" height="28" fill="currentColor" class="text-danger" viewBox="0 0 24 24">
                                        <circle cx="12" cy="12" r="10"/>
                                        <line x1="4" y1="4" x2="20" y2="20" stroke="white" stroke-width="2"/>
                                    </svg>
                                    @break

                                @case('reveal')
                                    <svg width="28" height="28" fill="currentColor" class="text-warning" viewBox="0 0 24 24">
                                        <path d="M12 4.5C7 4.5 2.73 8.11 1 12c1.73 3.89 6 7.5 11 7.5s9.27-3.61 11-7.5c-1.73-3.89-6-7.5-11-7.5zm0 12a4.5 4.5 0 110-9 4.5 4.5 0 010 9z"/>
                                    </svg>
                                    @break

                                @default
                                    <svg width="28" height="28" fill="currentColor" class="text-muted" viewBox="0 0 24 24">
                                        <circle cx="12" cy="12" r="10"/>
                                    </svg>
                            @endswitch
                        </div>

                        <div>
                            <strong>{{ $ability->name }}</strong><br>
                            <small class="text-light-emphasis">{{ $ability->description }}</small><br>
                            <small class="text-light-emphasis">⏱ Cooldown: {{ $ability->cooldown }}h</small>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col text-light-emphasis small fw-bold">
                    Abilități disponibile: <span class="text-muted">– momentan nimic –</span>
                </div>
            @endforelse
        </div>
    </div>
</div>
@endsection
