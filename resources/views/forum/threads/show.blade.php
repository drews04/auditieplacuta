@extends('layouts.app')

@section('title', $thread->title . ' - Forum')

@section('body_class', 'page-forum')

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/css/forum.css') }}">
@endpush

@section('content')
<div class="forum-container">
    <div class="container">
        <!-- Thread Header -->
        <div class="forum-header">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <div class="forum-category-badge mb-2">
                        {{ $thread->category->name }}
                    </div>
                    <h1>{{ $thread->title }}</h1>
                    <div class="forum-thread-meta mt-2">
                        <span class="forum-thread-author">
                            <i class="fas fa-user me-2"></i>{{ $thread->user->name ?? 'Utilizator' }}
                        </span>
                        <span class="forum-thread-time">
                            <i class="fas fa-clock me-2"></i>{{ $thread->created_at->diffForHumans() }}
                        </span>
                    </div>
                </div>
                <div class="col-md-4 text-md-end">
                    <div class="forum-thread-stats">
                        <div class="forum-stat">
                            <i class="fas fa-eye me-2"></i>
                            <span>{{ $thread->views_count }}</span>
                            <span>vizualizări</span>
                        </div>
                        <div class="forum-stat">
                            <i class="fas fa-comments me-2"></i>
                            <span>{{ $thread->replies_count }}</span>
                            <span>răspunsuri</span>
                        </div>
                    </div>
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

        <!-- Thread Body -->
        <div class="forum-thread-card">
            <div class="forum-thread-body">
                {!! nl2br(e($thread->body)) !!}
            </div>
        </div>

        <!-- Posts -->
        @if($thread->posts->count() > 0)
            <h3 class="text-light mb-3">Răspunsuri ({{ $thread->posts->count() }})</h3>
            @foreach($thread->posts as $post)
                @include('forum.partials.post', ['post' => $post])
            @endforeach
        @endif

        <!-- Reply Form -->
        @auth
            @if(!$thread->locked)
                <div class="forum-actions">
                    <h4 class="text-light mb-3">Adaugă un răspuns</h4>
                    <form action="{{ route('forum.posts.store') }}" method="POST">
                        @csrf
                        <input type="hidden" name="thread_id" value="{{ $thread->id }}">
                        
                        <div class="mb-3">
                            <textarea name="body" class="form-control forum-textarea" 
                                      rows="4" placeholder="Scrie răspunsul tău..." required minlength="2"></textarea>
                        </div>
                        
                        <div class="text-end">
                            <button type="submit" class="btn btn-new-thread">
                                <i class="fas fa-reply me-2"></i>Răspunde
                            </button>
                        </div>
                    </form>
                </div>
            @else
                <div class="forum-actions">
                    <div class="alert alert-warning">
                        <i class="fas fa-lock me-2"></i>
                        Acest thread este blocat. Nu mai pot fi adăugate răspunsuri.
                    </div>
                </div>
            @endif
        @else
            <div class="forum-actions">
                <div class="alert alert-info">
                    <i class="fas fa-info-circle me-2"></i>
                    <a href="{{ route('login') }}" class="alert-link">Conectează-te</a> pentru a răspunde.
                </div>
            </div>
        @endauth

        <!-- Back to Forum -->
        <div class="text-center mt-4">
            <a href="{{ route('forum.home') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left me-2"></i>Înapoi la Forum
            </a>
        </div>
    </div>
</div>
@endsection

@push('scripts')
    <script src="{{ asset('assets/js/forum.js') }}"></script>
@endpush
