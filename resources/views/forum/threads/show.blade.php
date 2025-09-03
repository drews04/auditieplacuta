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
                        <div class="forum-stat">
                            <button class="forum-like-btn" data-type="thread" data-id="{{ $thread->slug }}">
                                <i class="far fa-heart {{ $thread->likedBy(auth()->id()) ? 'is-liked' : '' }}"></i>
                                <span class="forum-like-count">{{ $thread->likes()->count() }}</span>
                            </button>
                        </div>
                    </div>

                    <!-- Thread actions (owner or admin) -->
                    <div class="mt-2 d-flex justify-content-end gap-2">
                        @can('update', $thread)
                            <a href="{{ route('forum.threads.edit', $thread) }}" class="btn btn-secondary btn-sm">Editează</a>
                        @endcan
                        @can('delete', $thread)
                            <form action="{{ route('forum.threads.destroy', $thread) }}" method="POST"
                                  onsubmit="return confirm('Sigur ștergi acest thread?');" class="d-inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-secondary btn-sm">Șterge</button>
                            </form>
                        @endcan
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
        @if($topPosts->count() > 0)
            <h3 class="text-light mb-3">Răspunsuri ({{ $thread->posts->count() }})</h3>
            @foreach($topPosts as $post)
                @include('forum.partials.post', ['post' => $post])
            @endforeach
        @endif

        @if($errors->any())
            <div class="alert alert-danger mb-3">
                <ul class="mb-0">
                    @foreach($errors->all() as $e)
                        <li>{{ $e }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <!-- Reply Form -->
        @auth
            @if(!$thread->locked)
                <div class="forum-actions">
                    <h4 class="text-light mb-3">Adaugă un răspuns</h4>
                    <div id="replying-pill" class="replying-pill d-none"></div>

                    <form id="reply-form" action="{{ route('forum.posts.store') }}" method="POST">
                        @csrf
                        <input type="hidden" name="thread_id" value="{{ $thread->id }}">
                        <input type="hidden" name="parent_id" id="parent_id" value="">

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
<script>
  // explicit bases, no regex shenanigans
  window.forumLikeThreadBase = "{{ url('/forum/like/thread') }}";
  window.forumLikePostBase   = "{{ url('/forum/like/post') }}";
</script>
<script src="{{ asset('js/forum.js') }}"></script>
@endpush
