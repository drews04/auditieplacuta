@extends('layouts.app')

@section('title', 'Forum - Auditie Placuta')

@section('body_class', 'page-forum')

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/css/forum.css') }}">
@endpush

@section('content')
<div class="forum-container">
    <div class="container">
        <!-- Forum Header -->
        <div class="forum-header">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h1>
                        @if(isset($currentCategory) && $currentCategory)
                            {{ $currentCategory->name }}
                        @else
                            Forum
                        @endif
                    </h1>
                    @if(isset($currentCategory) && $currentCategory)
                        <p class="text-muted mb-0">{{ $currentCategory->description }}</p>
                    @endif
                </div>
                <div class="col-md-4 text-md-end">
                    <a href="{{ route('forum.threads.create') }}" class="btn btn-new-thread">
                        <i class="fas fa-plus me-2"></i>Thread Nou
                    </a>
                </div>
            </div>
        </div>

        <!-- Success Messages -->
        @if(session('success'))
            <div class="alert alert-success mb-3">
                <i class="fas fa-check-circle me-2"></i>
                {{ session('success') }}
            </div>
        @endif

        <!-- Forum Actions -->
        <div class="forum-actions">
            <!-- Search and Sort Row -->
            <div class="row mb-3">
                <div class="col-md-8">
                    <div class="forum-search">
                        <input type="text" placeholder="Caută în thread-uri..." aria-label="Caută thread-uri">
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="forum-sort">
                        <select aria-label="Sortează thread-urile">
                            <option value="latest">Ultima Activitate</option>
                            <option value="replies">Cele Mai Multe Răspunsuri</option>
                        </select>
                    </div>
                </div>
            </div>
            
            <!-- Categories Row -->
            <div class="forum-categories">
                <a href="{{ route('forum.home') }}" 
                   class="forum-category-pill {{ !request()->route('category') ? 'active' : '' }}">
                    Toate
                </a>
                @foreach($categories as $category)
                    <a href="{{ route('forum.categories.show', $category->slug) }}" 
                       class="forum-category-pill {{ isset($currentCategory) && $currentCategory && $currentCategory->id === $category->id ? 'active' : '' }}">
                        {{ $category->name }}
                        <span class="badge bg-secondary ms-1">{{ $category->threads_count }}</span>
                    </a>
                @endforeach
            </div>
        </div>

        <!-- Threads List -->
        <div class="forum-threads">
            @forelse($threads as $thread)
                @include('forum.partials.thread_card', ['thread' => $thread])
            @empty
                <div class="forum-thread-card text-center">
                    <div class="text-muted">
                        <i class="fas fa-inbox fa-3x mb-3"></i>
                        <h4>Nu există thread-uri încă</h4>
                        <p>Fii primul care creează un thread în această categorie!</p>
                        @auth
                            <a href="{{ route('forum.threads.create') }}" class="btn btn-new-thread">
                                <i class="fas fa-plus me-2"></i>Creează Thread
                            </a>
                        @endauth
                    </div>
                </div>
            @endforelse
        </div>

        <!-- Pagination -->
        @if($threads->hasPages())
            <div class="forum-pagination">
                {{ $threads->links() }}
            </div>
        @endif
    </div>
</div>
@endsection

@push('scripts')
    <script src="{{ asset('assets/js/forum.js') }}"></script>
@endpush
