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
        $post = Post::create([
            'thread_id' => $request->thread_id,
            'user_id' => auth()->id(),
            'body' => $request->body,
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
