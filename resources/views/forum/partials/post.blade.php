<div class="forum-thread-card forum-post">
    <div class="forum-post-header mb-3">
        <div class="d-flex justify-content-between align-items-center">
            <div class="forum-thread-author">
                <i class="fas fa-user me-2"></i>
                <strong>{{ $post->user->name ?? 'Utilizator' }}</strong>
            </div>
            <div class="forum-thread-time">
                <i class="fas fa-clock me-2"></i>
                {{ $post->created_at->diffForHumans() }}
                @if($post->isEdited())
                    <small class="text-muted ms-2">(editat)</small>
                @endif
            </div>
        </div>
    </div>
    
    <div class="forum-post-body">
        {!! nl2br(e($post->body)) !!}
    </div>
</div>
