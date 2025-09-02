<div class="forum-thread-card" data-category="{{ $thread->category->name }}">
    <!-- Category Badge -->
    <div class="forum-category-badge">
        {{ $thread->category->name }}
    </div>
    
    <!-- Thread Title -->
    <div class="forum-thread-title">
        <a href="{{ route('forum.threads.show', $thread->slug) }}">
            {{ $thread->title }}
        </a>
    </div>
    
    <!-- Thread Excerpt -->
    <div class="forum-thread-excerpt">
        {{ Str::limit($thread->body, 150) }}
    </div>
    
    <!-- Thread Meta -->
    <div class="forum-thread-meta">
        <div class="forum-thread-author">
            <i class="fas fa-user me-2"></i>
            {{ $thread->user->name ?? 'Utilizator' }}
        </div>
        <div class="forum-thread-time">
            <i class="fas fa-clock me-2"></i>
            {{ $thread->created_at->diffForHumans() }}
        </div>
    </div>
    
    <!-- Thread Stats -->
    <div class="forum-thread-stats">
        <div class="forum-stat" data-stat="replies">
            <i class="fas fa-comments me-2"></i>
            <span class="forum-stat-value">{{ $thread->replies_count }}</span>
            <span>răspunsuri</span>
        </div>
        <div class="forum-stat" data-stat="views">
            <i class="fas fa-eye me-2"></i>
            <span class="forum-stat-value">{{ $thread->views_count }}</span>
            <span>vizualizări</span>
        </div>
    </div>
    
    <!-- Last Activity -->
    @if($thread->last_posted_at)
        <div class="forum-thread-activity">
            <i class="fas fa-history me-2"></i>
            Ultima activitate {{ $thread->last_posted_at->diffForHumans() }}
            @if($thread->lastPostUser)
                de <strong>{{ $thread->lastPostUser->name }}</strong>
            @endif
        </div>
    @endif
</div>
