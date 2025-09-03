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
    
    <div class="forum-post-actions mt-3 d-flex justify-content-between align-items-center">
        <div class="d-flex gap-2">
            <button class="forum-like-btn" data-type="post" data-id="{{ $post->id }}">
                <i class="fas fa-heart{{ $post->likedBy(auth()->id()) ? '' : '-o' }}"></i>
                <span class="forum-like-count">{{ $post->likes()->count() }}</span>
            </button>
            @auth
                <button class="btn btn-sm btn-outline-secondary forum-reply-btn" 
                        data-post-id="{{ $post->id }}" 
                        data-user-name="{{ $post->user->name ?? 'Utilizator' }}">
                    <i class="fas fa-reply me-1"></i>Răspunde
                </button>
            @endauth
        </div>
    </div>
    
    @if($post->children->count() > 0)
        <div class="forum-post-children mt-3">
            @foreach($post->children as $child)
                <div class="forum-thread-card forum-post forum-post-reply">
                    <div class="forum-post-header mb-2">
                        <div class="d-flex justify-content-between align-items-center">
                            <div class="forum-thread-author">
                                <i class="fas fa-user me-2"></i>
                                <strong>{{ $child->user->name ?? 'Utilizator' }}</strong>
                            </div>
                            <div class="forum-thread-time">
                                <i class="fas fa-clock me-2"></i>
                                {{ $child->created_at->diffForHumans() }}
                                @if($child->isEdited())
                                    <small class="text-muted ms-2">(editat)</small>
                                @endif
                            </div>
                        </div>
                    </div>
                    
                    <div class="forum-post-body">
                        {!! nl2br(e($child->body)) !!}
                    </div>
                    
                    <div class="forum-post-actions mt-2">
                        <button class="forum-like-btn" data-type="post" data-id="{{ $child->id }}">
                            <i class="fas fa-heart{{ $child->likedBy(auth()->id()) ? '' : '-o' }}"></i>
                            <span class="forum-like-count">{{ $child->likes()->count() }}</span>
                        </button>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>
