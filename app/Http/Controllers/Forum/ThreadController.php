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
        
        $thread->load(['category', 'user', 'posts.user', 'posts.children.user', 'likes']);
        
        return view('forum.threads.show', compact('thread'));
    }
}
