<?php

namespace App\Http\Controllers\Forum;

use App\Http\Controllers\Controller;
use App\Models\Forum\Post;
use App\Models\Forum\Thread;
use App\Http\Requests\Forum\StorePostRequest;

class PostController extends Controller
{
    public function store(StorePostRequest $request)
    {
        // Validate parent_id constraints
        if ($request->parent_id) {
            $parent = Post::find($request->parent_id);
            
            // Check if parent belongs to the same thread
            if ($parent->thread_id !== $request->thread_id) {
                return back()->withErrors(['parent_id' => 'Post-ul părinte trebuie să aparțină aceluiași thread.']);
            }
            
            // Check if parent is not already a reply (max depth = 1)
            if ($parent->parent_id !== null) {
                return back()->withErrors(['parent_id' => 'Nu se pot crea răspunsuri mai adânci de un nivel.']);
            }
        }
        
        $post = Post::create([
            'thread_id' => $request->thread_id,
            'user_id' => auth()->id(),
            'body' => $request->body,
            'parent_id' => $request->parent_id,
        ]);
        
        // Update thread counts and last activity
        $thread = Thread::find($request->thread_id);
        $thread->increment('replies_count');
        $thread->update([
            'last_posted_at' => now(),
            'last_post_user_id' => auth()->id(),
        ]);
        
        return redirect()->route('forum.threads.show', $thread->slug)
            ->with('success', 'Răspunsul a fost adăugat cu succes!');
    }
}
