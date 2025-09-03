<?php

namespace App\Http\Controllers\Forum;

use App\Http\Controllers\Controller;
use App\Models\Forum\Post;
use App\Models\Forum\Thread;
use App\Http\Requests\Forum\StorePostRequest;

class PostController extends Controller
{
        public function store(\Illuminate\Http\Request $request)
    {
        $data = $request->validate([
            'thread_id' => ['required','integer','exists:forum_threads,id'],
            'body'      => ['required','string','min:2'],
            'parent_id' => ['nullable','integer','exists:forum_posts,id'],
        ]);

        $thread = \App\Models\Forum\Thread::findOrFail($data['thread_id']);

        // Enforce: same thread + max depth = 1 (only reply to top-level posts)
        $parentId = $data['parent_id'] ?? null;
        if ($parentId) {
            $parent = \App\Models\Forum\Post::find($parentId);
            if (!$parent || $parent->thread_id !== $thread->id || !is_null($parent->parent_id)) {
                return redirect()->route('forum.threads.show', $thread->slug)
                    ->with('danger', 'Nu poți răspunde la acest mesaj.')
                    ->withInput();
            }
        }

        $post = \App\Models\Forum\Post::create([
            'thread_id' => $thread->id,
            'user_id'   => $request->user()->id,
            'body'      => $data['body'],
            'parent_id' => $parentId,
        ]);

        // Update thread counts and last activity
        $thread->increment('replies_count');
        $thread->update([
            'last_posted_at'   => now(),
            'last_post_user_id'=> $request->user()->id,
        ]);

        // Redirect to new post anchor
        return redirect(route('forum.threads.show', $thread->slug) . '#post-' . $post->id)
            ->with('success', 'Răspunsul a fost adăugat!');
    }

    public function edit(\App\Models\Forum\Post $post)
    {
        $this->authorize('update', $post);
        return view('forum.posts.edit', compact('post'));
    }

    public function update(\Illuminate\Http\Request $request, \App\Models\Forum\Post $post)
    {
        $this->authorize('update', $post);

        $data = $request->validate([
            'body' => ['required','string','min:2'],
        ]);

        $post->update([
            'body'      => $data['body'],
            'edited_at' => now(),
        ]);

        $thread = $post->thread;
        return redirect(route('forum.threads.show', $thread->slug) . '#post-' . $post->id)
            ->with('success', 'Răspunsul a fost actualizat.');
    }

        public function destroy(\App\Models\Forum\Post $post)
    {
        $this->authorize('delete', $post);

        $thread = $post->thread;

        // ids: post + children
        $childIds = $post->children()->pluck('id')->all();
        $ids = array_merge([$post->id], $childIds);

        // delete likes for all these posts
        \App\Models\Forum\Like::where('likeable_type', \App\Models\Forum\Post::class)
            ->whereIn('likeable_id', $ids)->delete();

        // hard delete children then the post
        \App\Models\Forum\Post::whereIn('id', $childIds)->forceDelete();
        $post->forceDelete();

        // adjust replies_count
        $thread->replies_count = max(0, (int)$thread->replies_count - count($ids));
        $thread->save();

        return redirect()->route('forum.threads.show', $thread->slug)
            ->with('success', 'Răspunsul a fost șters.');
    }


    
}
