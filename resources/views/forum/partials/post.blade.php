{{-- resources/views/forum/partials/post.blade.php --}}
@php $flat = $flat ?? false; @endphp
<div class="forum-thread-card forum-post" id="post-{{ $post->id }}">
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

    <div class="forum-post-body post-body">
        {!! nl2br(e($post->body)) !!}
    </div>

    <div class="forum-post-actions mt-3 d-flex align-items-center gap-2">
        <button class="forum-like-btn" data-type="post" data-id="{{ $post->id }}">
            <i class="far fa-heart {{ $post->likedBy(auth()->id()) ? 'is-liked' : '' }}"></i>
            <span class="forum-like-count">{{ $post->likes()->count() }}</span>
        </button>

        @auth
        <button class="forum-reply-btn"
            data-post-id="{{ $post->parent_id ? $post->parent_id : $post->id }}"
            data-user-name="{{ $post->user->name ?? 'Utilizator' }}">↩ Răspunde</button>
        @endauth

        <div class="ms-auto d-flex gap-2">
            @can('update', $post)
                <a href="{{ route('forum.posts.edit', $post) }}" class="btn btn-secondary btn-sm">Editează</a>
            @endcan
            @can('delete', $post)
                <form action="{{ route('forum.posts.destroy', $post) }}" method="POST"
                      onsubmit="return confirm('Ștergi acest răspuns?');" class="d-inline">
                    @csrf @method('DELETE')
                    <button type="submit" class="btn btn-secondary btn-sm">Șterge</button>
                </form>
            @endcan
        </div>
    </div>

    @if($post->children && $post->children->count() > 0)
        <div class="forum-post-children mt-3">
            @foreach($post->children as $child)
                <div class="forum-thread-card forum-post forum-post-reply" id="post-{{ $child->id }}">
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

                    <div class="forum-post-body post-body">
                        @if($child->parent && $child->parent->user)
                            <div class="replying-to-pill mb-2">
                                Răspunzi lui <strong>&#64;{{ $child->parent->user->name }}</strong>
                            </div>
                        @endif
                        {!! nl2br(e($child->body)) !!}
                    </div>

                    <div class="forum-post-actions mt-2 d-flex align-items-center gap-2">
                        <button class="forum-like-btn" data-type="post" data-id="{{ $child->id }}">
                            <i class="far fa-heart {{ $child->likedBy(auth()->id()) ? 'is-liked' : '' }}"></i>
                            <span class="forum-like-count">{{ $child->likes()->count() }}</span>
                        </button>
                        @auth
                        <button class="forum-reply-btn ms-2"
                                data-post-id="{{ $child->parent_id ?: $child->id }}"
                                data-user-name="{{ $child->user->name ?? 'Utilizator' }}">↩ Răspunde</button>
                        @endauth

                        <div class="ms-auto d-flex gap-2">
                            @can('update', $child)
                                <a href="{{ route('forum.posts.edit', $child) }}" class="btn btn-secondary btn-sm">Editează</a>
                            @endcan
                            @can('delete', $child)
                                <form action="{{ route('forum.posts.destroy', $child) }}" method="POST"
                                      onsubmit="return confirm('Ștergi acest răspuns?');" class="d-inline">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-secondary btn-sm">Șterge</button>
                                </form>
                            @endcan
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>
