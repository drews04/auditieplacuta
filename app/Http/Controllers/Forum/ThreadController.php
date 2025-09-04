<?php

namespace App\Http\Controllers\Forum;

use App\Http\Controllers\Controller;
use App\Http\Requests\Forum\StoreThreadRequest;
use App\Models\Forum\Category;
use App\Models\Forum\Like;
use App\Models\Forum\Post;
use App\Models\Forum\Thread;
use App\Models\Forum\ViewHit;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ThreadController extends Controller
{
    public function index(?Category $category = null)
    {
        $categories = Category::orderBy('name')->get();

        $query = Thread::with(['category', 'user'])
            ->visible()
            ->orderByDesc('pinned')
            ->orderByDesc('updated_at');

        if ($category) {
            $query->where('category_id', $category->id);
        }

        // Threads per page: 20
        $threads = $query->paginate(20)->withQueryString();
        $threads->onEachSide(1); // show one page number on each side

        // Pass the selected category (if any) to the view
        $currentCategory = $category;

        return view('forum.home', compact('categories', 'threads', 'currentCategory'));
    }

    public function create()
    {
        $categories = Category::orderBy('name')->get();
        return view('forum.threads.create', compact('categories'));
    }

    public function store(StoreThreadRequest $request)
    {
        $slug = Str::slug($request->title) . '-' . Str::random(6);

        $thread = Thread::create([
            'category_id'       => $request->category_id,
            'user_id'           => auth()->id(),
            'title'             => $request->title,
            'slug'              => $slug,
            'body'              => $request->body,
            'last_posted_at'    => now(),
            'last_post_user_id' => auth()->id(),
        ]);

        // Update category thread count
        Category::whereKey($thread->category_id)->increment('threads_count');

        return redirect()
            ->route('forum.threads.show', $thread->slug)
            ->with('success', 'Thread-ul a fost creat cu succes!');
    }

    public function show(Thread $thread) // expects {thread:slug} binding
    {
        // Increment view count with deduplication (user or session, 6h window)
        $keyUser = auth()->id();
        $keySess = session()->getId();

        $exists = ViewHit::where('thread_id', $thread->id)
            ->where(function ($query) use ($keyUser, $keySess) {
                $query->when($keyUser, fn ($q) => $q->where('user_id', $keyUser))
                      ->orWhere('session_id', $keySess);
            })
            ->where('created_at', '>', now()->subHours(6))
            ->exists();

        if (!$exists) {
            ViewHit::create([
                'thread_id'  => $thread->id,
                'user_id'    => $keyUser,
                'session_id' => $keySess,
                'ip'         => request()->ip(),
            ]);
            $thread->increment('views_count');
        }

        $thread->load([
            'category',
            'user',
            'posts.user',
            'posts.likes',
            'posts.children.user',
            'posts.children.likes',
            'likes',
        ]);

        // Top-level replies per page: 15 (children are eager-loaded)
        $topPosts = $thread->posts()
            ->whereNull('parent_id')
            ->orderBy('created_at')
            ->with(['user', 'likes', 'children.user', 'children.likes'])
            ->paginate(50)
            ->withQueryString();

        $topPosts->onEachSide(1);

        return view('forum.threads.show', compact('thread', 'topPosts'));
    }

    public function edit(Thread $thread) // expects {thread:slug}
    {
        $this->authorize('update', $thread);

        $categories = Category::orderBy('name')->get();

        return view('forum.threads.edit', compact('thread', 'categories'));
    }

    public function update(Request $request, Thread $thread) // expects {thread:slug}
    {
        $this->authorize('update', $thread);

        $data = $request->validate([
            'title'       => ['required', 'string', 'min:4', 'max:140'],
            'body'        => ['required', 'string', 'min:10'],
            'category_id' => ['nullable', 'integer', 'exists:forum_categories,id'],
        ]);

        $thread->update([
            'title'       => $data['title'],
            'body'        => $data['body'],
            'category_id' => $data['category_id'] ?? $thread->category_id,
        ]);

        return redirect()
            ->route('forum.threads.show', $thread->slug)
            ->with('success', 'Thread-ul a fost actualizat.');
    }

    public function destroy(Thread $thread) // expects {thread:slug}
    {
        $this->authorize('delete', $thread);

        // collect child post ids
        $postIds = $thread->posts()->pluck('id');

        // delete likes (posts + thread)
        Like::where('likeable_type', Post::class)
            ->whereIn('likeable_id', $postIds)
            ->delete();

        Like::where('likeable_type', Thread::class)
            ->where('likeable_id', $thread->id)
            ->delete();

        // delete views
        ViewHit::where('thread_id', $thread->id)->delete();

        // delete posts (force)
        Post::whereIn('id', $postIds)->forceDelete();

        // delete thread (force)
        $thread->forceDelete();

        // keep category counts tidy
        Category::whereKey($thread->category_id)->decrement('threads_count');

        return redirect()
            ->route('forum.home')
            ->with('success', 'Thread-ul a fost șters.');
    }
}
