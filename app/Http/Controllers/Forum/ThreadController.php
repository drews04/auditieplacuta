<?php

namespace App\Http\Controllers\Forum;

use App\Http\Controllers\Controller;
use App\Models\Forum\Category;
use App\Models\Forum\Thread;
use App\Models\Forum\ViewHit;
use App\Http\Requests\Forum\StoreThreadRequest;
use Illuminate\Support\Str;
use Illuminate\Http\Request;

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
        
        $threads = $query->paginate(10);
        
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
            'category_id' => $request->category_id,
            'user_id' => auth()->id(),
            'title' => $request->title,
            'slug' => $slug,
            'body' => $request->body,
            'last_posted_at' => now(),
            'last_post_user_id' => auth()->id(),
        ]);
        
        // Update category thread count
        Category::whereKey($thread->category_id)->increment('threads_count');
        
        return redirect()->route('forum.threads.show', $thread->slug)
            ->with('success', 'Thread-ul a fost creat cu succes!');
    }
    
    public function show(Thread $thread)
    {
        // Increment view count with deduplication
        $keyUser = auth()->id();
        $keySess = session()->getId();
        
        $exists = ViewHit::where('thread_id', $thread->id)
            ->where(function($query) use ($keyUser, $keySess) {
                $query->when($keyUser, fn($q) => $q->where('user_id', $keyUser))
                      ->orWhere('session_id', $keySess);
            })
            ->where('created_at', '>', now()->subHours(6))
            ->exists();
        
        if (!$exists) {
            ViewHit::create([
                'thread_id' => $thread->id,
                'user_id' => $keyUser,
                'session_id' => $keySess,
                'ip' => request()->ip()
            ]);
            $thread->increment('views_count');
        }
        
        $thread->load(['category', 'user', 'posts.user', 'posts.likes', 'posts.children.user', 'posts.children.likes', 'likes']);
        
        $topPosts = $thread->posts()->whereNull('parent_id')->orderBy('created_at')->get();
        
        return view('forum.threads.show', compact('thread', 'topPosts'));
    }

    public function edit(\App\Models\Forum\Thread $thread)
{
    $this->authorize('update', $thread);
    $categories = \App\Models\Forum\Category::orderBy('name')->get();
    return view('forum.threads.edit', compact('thread','categories'));
}

    public function update(\Illuminate\Http\Request $request, \App\Models\Forum\Thread $thread)
    {
        $this->authorize('update', $thread);

        $data = $request->validate([
            'title' => ['required','string','min:4','max:140'],
            'body'  => ['required','string','min:10'],
            // optional: allow moving category
            'category_id' => ['nullable','integer','exists:forum_categories,id'],
        ]);

        $thread->update([
            'title'       => $data['title'],
            'body'        => $data['body'],
            'category_id' => $data['category_id'] ?? $thread->category_id,
        ]);

        return redirect()->route('forum.threads.show', $thread->slug)
            ->with('success', 'Thread-ul a fost actualizat.');
    }

        public function destroy(\App\Models\Forum\Thread $thread)
    {
        $this->authorize('delete', $thread);

        // collect child post ids
        $postIds = $thread->posts()->pluck('id');

        // delete likes (posts + thread)
        \App\Models\Forum\Like::where('likeable_type', \App\Models\Forum\Post::class)
            ->whereIn('likeable_id', $postIds)->delete();
        \App\Models\Forum\Like::where('likeable_type', \App\Models\Forum\Thread::class)
            ->where('likeable_id', $thread->id)->delete();

        // delete views
        \App\Models\Forum\ViewHit::where('thread_id', $thread->id)->delete();

        // delete posts (force)
        \App\Models\Forum\Post::whereIn('id', $postIds)->forceDelete();

        // delete thread (force)
        $thread->forceDelete();

        // keep category counts tidy (if you use it)
        \App\Models\Forum\Category::whereKey($thread->category_id)->decrement('threads_count');

        return redirect()->route('forum.home')->with('success', 'Thread-ul a fost șters.');
    }

}
